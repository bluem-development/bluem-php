<?php

/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

require_once 'Emandates.php';
require_once 'BluemResponse.php';

use Carbon\Carbon;
use Exception;
use Selective\XmlDSig\XmlSignatureValidator;

libxml_use_internal_errors(true);

if (!defined("BLUEM_ENVIRONMENT_PRODUCTION")) {
	define("BLUEM_ENVIRONMENT_PRODUCTION", "prod");
}
if (!defined("BLUEM_ENVIRONMENT_TESTING")) {
	define("BLUEM_ENVIRONMENT_TESTING", "test");
}
if (!defined("BLUEM_ENVIRONMENT_ACCEPTANCE")) {
	define("BLUEM_ENVIRONMENT_ACCEPTANCE", "acc");
}

/**
 * BlueM Integration main class
 */
class Integration
{
	private $configuration;

	public $environment;

	/**
	 * Constructs a new instance.
	 */
	function __construct($configuration = null)
	{
		if (is_null($configuration)) {
			throw new Exception("No valid configuration given to instantiate Bluem Integration");
			exit;
		}

		$this->configuration = $configuration;

		if ($this->configuration->environment === BLUEM_ENVIRONMENT_PRODUCTION) {
			$this->configuration->accessToken = $configuration->production_accessToken;
		} elseif ($this->configuration->environment === BLUEM_ENVIRONMENT_TESTING) {
			$this->configuration->accessToken = $configuration->test_accessToken;
		}

		$this->environment = $this->configuration->environment;

		// this is given by the bank (default 0)
		$this->configuration->merchantSubID = "0";
	}

	/**-------------- MANDATE SPECIFIC FUNCTIONS --------------*/

	public function CreateMandateRequest(
		$customer_id,
		$order_id,
		$request_type = "default",
		$simple_redirect_url = ""
	) {
		if (is_null($customer_id)) {
			throw new Exception("Customer ID Not set", 1);
		}
		if (is_null($order_id)) {
			throw new Exception("Order ID Not set", 1);
		}

		$r = new EmandateBluemRequest(
			$this->configuration,
			$customer_id,
			$order_id,
			$this->CreateMandateID($order_id, $customer_id),
			($this->configuration->environment == BLUEM_ENVIRONMENT_TESTING &&
				isset($this->configuration->expected_return) ?
				$this->configuration->expected_return : ""),
			$request_type,
			$simple_redirect_url
		);
		return $r;
	}

	public function Mandate(
		$customer_id,
		$order_id,
		$request_type = "default",
		$simple_redirect_url = ""
	) {
		$this->PerformRequest(
			$this->CreateMandateRequest(
				$customer_id,
				$order_id,
				$request_type,
				$simple_redirect_url
			)
		);
	}


	public function MandateStatus($mandateID, $entranceCode)
	{

		$r = new EMandateStatusBluemRequest(
			$this->configuration,
			$mandateID,
			$entranceCode,
			($this->configuration->environment == BLUEM_ENVIRONMENT_TESTING &&
				isset($this->configuration->expected_return) ?
				$this->configuration->expected_return : "")
		);

		$response = $this->PerformRequest($r);

		return $response;
	}

	/**
	 * Create a mandate ID in the required structure, based on the order ID, customer ID and the current timestamp.
	 * @param String $order_id    The order ID
	 * @param String $customer_id The customer ID
	 */
	public function CreateMandateID(String $order_id, String $customer_id): String
	{
		// veteranen search team
		if ($this->configuration->senderID === "S1300") {
			return "M" . Carbon::now()->format('YmdHis');
		}
		// nextdeli etc.
		return substr($customer_id . Carbon::now()->format('Ymd') . $order_id, 0, 35);
	}





	/**-------------- LEGACY FUNCTIONS --------------*/
	// To be deprecated by generic / universal functions

	/**
	 * Request a transaction status for any type of transaction
	 * 
	 * @param [type] $mandateID [description]
	 */
	public function RequestTransactionStatus($mandateID, $entranceCode)
	{
		return $this->MandateStatus($mandateID, $entranceCode);
	}

	/**
	 * Creates a new test transaction and in case of success, return the link to redirect to to get to the BlueM eMandate environment.
	 * @param int $customer_id The Customer ID
	 * @param int $order_id    The Order ID
	 */
	public function CreateNewTransaction(
		$customer_id,
		$order_id,
		$request_type = "default",
		$simple_redirect_url = ""
	) {
		return $this->CreateMandate($customer_id, $order_id, $request_type, $simple_redirect_url);
	}

	/** Universal Functions */
	/**
	 * Generate an entrance code based on the current date and time.
	 */
	public function CreateEntranceCode(): String
	{
		return Carbon::now()->format("YmdHis") . "000";
	}

	/**
	 * Perform a request to the BlueM API given a request object and return its response
	 * @param BluemRequest $transaction_request The Request Object
	 */
	public function PerformRequest(BluemRequest $transaction_request)
	{

		$now = Carbon::now();

		$xttrs_filename = $transaction_request->transaction_code . "-{$this->configuration->senderID}-BSP1-" . $now->format('YmdHis') . "000.xml";

		$xttrs_date = $now->format("D, d M Y H:i:s") . " GMT";
		// conform Rfc1123 standard in GMT time

		$req = new \HTTP_Request2();
		$req->setUrl($transaction_request->HttpRequestUrl());

		$req->setMethod(\HTTP_Request2::METHOD_POST);

		$req->setHeader("Content-Type", "application/xml; type=" . $transaction_request->transaction_code . "; charset=UTF-8");
		$req->setHeader('x-ttrs-date', $xttrs_date);
		$req->setHeader('x-ttrs-files-count', '1');
		$req->setHeader('x-ttrs-filename', $xttrs_filename);

		$req->setBody($transaction_request->XmlString());

		try {
			$http_response = $req->send();
			// var_dump($http_response->getStatus());

			switch ($http_response->getStatus()) {
				case 200: {
						$response = new BluemResponse($http_response->getBody());
						if (!$response->Status()) {

							return new ErrorBluemResponse("Error: " . ($response->Error()->ErrorMessage));
						}
						return $response;

						break;
					}
				case 400: {
					// XML message with a description of the error:
					// PaymentErrorResponse
					// or EMandateErrorResponse, IDentityErrorResponse or
					// IBANCheckErrorResponse

						return new ErrorBluemResponse('Your request was not formed correctly.');
						break;
					}
				case 401: {
						return new ErrorBluemResponse('Unauthorized: check your access credentials.');
						break;
					}
				case 500: {
						return new ErrorBluemResponse('An unrecoverable error at the server side occurred while processing the request');
						break;
					}
				default: {
						return new ErrorBluemResponse('Unexpected / erroneous response (code ' . $http_response->getStatus() . ')');
						break;
					}
			}
		} catch (\HTTP_Request2_Exception $e) {
			$error = new ErrorBluemResponse('Error: ' . $e->getMessage());
			return $error;
		}
	}


	public function GetMaximumAmountFromTransactionResponse($response)
	{

		if (isset($response->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->MaxAmount)) {

			return (object) [
				'amount' => (float) ($response->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->MaxAmount . ""),
				'currency' => 'EUR'
			];
		}
		return (object) ['amount' => (float) 0.0, 'currency' => 'EUR'];
	}




	/**
	 * Webhook for BlueM Mandate signature verification procedure
	 * @return [type] [description]
	 */
	public function Webhook()
	{

		/* Senders provide Bluem with a webhook URL. The URL will be checked for consistency and validity and will not be stored if any of the checks fails. The following checks will be performed:
	
			*/

		// todo: URL must start with https://


		// ONLY Accept post requests
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(400);
			exit();
		}

		// An empty POST to the URL (normal HTTP request) always has to respond with HTTP 200 OK
		$postData = file_get_contents('php://input');
		// var_dump($postData);
		if ($postData === "") {
			// echo "NO POST";
			http_response_code(200);
			exit();
		}

		// check content type; it has to be: "Content-type", "text/xml; charset=UTF-8"


		// Parsing XML data from POST body
		try {
			$xml_input = new \SimpleXMLElement($postData);
		} catch (Exception $e) {
			http_response_code(400); 		// could not parse XML
			exit();
		}

		// check if signature is valid in postdata
		if (!$this->validateWebhookSignature($postData)) {
			http_response_code(400);
			// echo 'The XML signature is not valid.';
			// echo PHP_EOL;
			exit;
		}

		// valid!
		// echo $postData;
		// echo "<hr>Input";
		// var_dump($xml_input);
		// die();
		if (!isset($xml_input->EMandateInterface->EMandateStatusUpdate)) {
			http_response_code(400);
			exit;
		}

		$status_update = $xml_input->EMandateInterface->EMandateStatusUpdate;
		return $status_update;
	}


	public function validateWebhookSignature($xml_input)
	{
		$temp_file = tmpfile();
		fwrite($temp_file, $xml_input);
		$temp_file_path = stream_get_meta_data($temp_file)['uri'];

		$signatureValidator = new XmlSignatureValidator();

		// @todo Check if keyfile has to be chosen according to env
		// if ($this->configuration->environment === BLUEM_ENVIRONMENT_TESTING) {
		// $public_key_file = "webhook.bluem.nl_pub_cert_test.crt";
		// } else {
		// $public_key_file = "webhook.bluem.nl_pub_key_production.crt";
		// }

		$public_key_file = "bluem_nl.crt";
		$public_key_file_path = ABSPATH . "wp-content/plugins/bluem-woocommerce/keys/" . $public_key_file;
		// TODO: put the key in a different folder, relative to this PHP library


		try {
			$signatureValidator->loadPublicKeyFile($public_key_file_path);
		} catch (\Throwable $th) {
			return false;
			// echo "Fout: " . $th->getMessage();
			// exit;
		}

		$isValid = $signatureValidator->verifyXmlFile($temp_file_path);
		fclose($temp_file);

		if ($isValid) {
			return true;
		}
		return false;
	}
}
