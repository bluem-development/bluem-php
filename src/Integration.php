<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

// require __DIR__ . '/../vendor/autoload.php';

require_once 'Emandates.php';
require_once 'Payments.php';
require_once 'Identity.php';
require_once 'Iban.php';
require_once 'BluemResponse.php';
require_once 'BluemRequest.php';

require_once 'BIC.php';
require_once 'Context.php';



use Carbon\Carbon;
use Exception;
use HTTP_Request2 as BluemHttpRequest;
use Selective\XmlDSig\XmlSignatureValidator;
use Throwable;

// libxml_use_internal_errors(false);

if (!defined("BLUEM_ENVIRONMENT_PRODUCTION")) {
    define("BLUEM_ENVIRONMENT_PRODUCTION", "prod");
}
if (!defined("BLUEM_ENVIRONMENT_TESTING")) {
    define("BLUEM_ENVIRONMENT_TESTING", "test");
}
if (!defined("BLUEM_ENVIRONMENT_ACCEPTANCE")) {
    define("BLUEM_ENVIRONMENT_ACCEPTANCE", "acc");
}
if (!defined("BLUEM_STATIC_MERCHANT_ID")) {
    define("BLUEM_STATIC_MERCHANT_ID", "0020009469");
}

/**
 * BlueM Integration main class
 */
class Integration
{
    private $_config;

    public $environment;

    /**
     * Constructs a new instance.
     */
    function __construct($_config = null)
    {
        if (is_null($_config)) {
            throw new Exception("No valid configuration given to instantiate Bluem Integration");
            exit;
        }
        // validating configuration
        if (!in_array(
            $_config->environment,
            [
                BLUEM_ENVIRONMENT_TESTING, BLUEM_ENVIRONMENT_ACCEPTANCE, BLUEM_ENVIRONMENT_PRODUCTION
            ]
        )
        ) {
            throw new Exception("Invalid environment setting, should be either 'test', 'acc' or 'prod'");
        }

        if (!isset($_config->localInstrumentCode)
            || !in_array(
                $_config->localInstrumentCode,
                ['B2B', 'CORE']
            )
        ) {
            // default localInstrumentCode
            $_config->localInstrumentCode = "CORE";
        }
        $this->_config = $_config;

        if ($this->_config->environment === BLUEM_ENVIRONMENT_PRODUCTION) {

            $this->_config->accessToken = $_config->production_accessToken;
            // @todo consider throwing an exception if these tokens are missing.

        } elseif ($this->_config->environment === BLUEM_ENVIRONMENT_TESTING) {

            $this->_config->accessToken = $_config->test_accessToken;
            // @todo consider throwing an exception if these tokens are missing.

            // hardcoded merchantID in case of test.
            // It is always the bluem merchant ID then.
            $this->merchantID = BLUEM_STATIC_MERCHANT_ID;
        }

        $this->environment = $this->_config->environment;

        // @todo Only use one environment variable. Right now it is saved in both $this->environment and $this->_config->environment

        // this is given by the bank (default 0)
        $this->_config->merchantSubID = "0";

        // if an invalid possible return status is given, set it to a default value (for testing purposes only)
        $possibleReturnStatuses = ["none", "success", "cancelled", "expired", "failure", "open", "pending"];
        if($this->_config->expectedReturnStatus!=="" && !in_array($this->_config->expectedReturnStatus,$possibleReturnStatuses)) {
            $this->_config->expectedReturnStatus = "success";
        }


        // @todo get this from settings in the future
    }

    /**-------------- MANDATE SPECIFIC FUNCTIONS --------------*/

    /**
     * Create a Mandate Request given a customer ID, order ID
     * and Mandate ID and return the request object
     * WITHOUT sending it
     *
     * @param $customer_id
     * @param $order_id
     * @param boolean $mandate_id
     *
     */
    public function CreateMandateRequest(
        $customer_id,
        $order_id,
        $mandate_id = false
    ) {
        if (is_null($customer_id)) {
            throw new Exception("Customer ID Not set", 1);
        }
        if (is_null($order_id)) {
            throw new Exception("Order ID Not set", 1);
        }

        if ($mandate_id === false) {
            $mandate_id = $this->CreateMandateID($order_id, $customer_id);
        }

        $r = new EmandateBluemRequest(
            $this->_config,
            $customer_id,
            $order_id,
            $mandate_id,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : "")
        );
        return $r;
    }

    /**
     * Create a Mandate Request given a customer ID, order ID
     * and Mandate ID and return the request object,
     * sending it and returning the response
     *
     * @param $customer_id
     * @param $order_id
     * @param $mandate_id
     * @return void
     */
    public function Mandate(
        $customer_id,
        $order_id,
        $mandate_id = false
    ) {
        $_request = $this->CreateMandateRequest(
            $customer_id,
            $order_id,
            $mandate_id
        );
        $response = $this->PerformRequest(
            $_request
        );
        return $response;
    }


    /**
     * Retrieving a mandate request's status based on a mandate ID and an entrance Code, and returning the response
     *
     * @param $mandateID
     * @param $entranceCode
     */
    public function MandateStatus($mandateID, $entranceCode)
    {

        $r = new EMandateStatusBluemRequest(
            $this->_config,
            $mandateID,
            $entranceCode,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : "")
        );

        $response = $this->PerformRequest($r);
        return $response;
    }

    /**
     * Create a mandate ID in the required structure, based on the order ID, customer ID and the current timestamp.
     *
     * @param String $order_id    The order ID
     * @param String $customer_id The customer ID
     */
    public function CreateMandateID(String $order_id, String $customer_id): String
    {
        // veteranen search team, specific
        if ($this->_config->senderID === "S1300") {
            return "M" . Carbon::now()->timezone('Europe/Amsterdam')->format('YmdHis');
        }

        // @todo create a future interface where one can select options how mandate IDs are formed

        // nextdeli etc.
        return substr($customer_id . Carbon::now()->timezone('Europe/Amsterdam')->format('Ymd') . $order_id, 0, 35);
    }


    /**
     * For mandates only: retreive the maximum amount from
     * the AcceptanceReport to use in parsing and validating
     * mandates in webshop context
     *
     * @param $response
     * @return void
     */
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

    /**-------------- PAYMENT SPECIFIC FUNCTIONS --------------*/
    /**
     * Create a payment request object
     *
     * @param String $description
     * @param  $debtorReference
     * @param Float $amount
     * @param  $dueDateTime
     * @param String $currency
     * @param  $entranceCode
     * @return PaymentBluemRequest
     */
    public function CreatePaymentRequest(
        String $description,
        $debtorReference,
        Float $amount,
        $dueDateTime = null,
        String $currency = "EUR",
        $entranceCode = null,
        $debtorReturnURL = ""
    ): PaymentBluemRequest {

        if (is_null($entranceCode)) {
            $entranceCode = $this->CreateEntranceCode();
        }

        $r = new PaymentBluemRequest(
            $this->_config,
            $description,
            $debtorReference,
            $amount,
            $dueDateTime,
            $currency,
            $this->CreatePaymentTransactionID($debtorReference),
            $entranceCode,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : ""),
                $debtorReturnURL
        );
        return $r;
    }

    /**
     * Create a payment request and perform it, returning the response
     *
     * @param  $description
     * @param  $debtorReference
     * @param  $amount
     * @param  $dueDateTime
     * @param string $currency
     * @param  $entranceCode
     * @return void
     */
    public function Payment(
        string $description,
        $debtorReference,
        $amount,
        $dueDateTime = null,
        string $currency = "EUR",
        $entranceCode = null
    ) {

        if (is_null($entranceCode)) {
            $entranceCode = $this->CreateEntranceCode();
        }
        return $this->PerformRequest(
            $this->CreatePaymentRequest(
                $description,
                $debtorReference,
                $amount,
                $dueDateTime,
                $currency,
                $entranceCode
            )
        );
    }

    /**
     * Retrieve the status of a payment request, based on transactionID and Entrance Code
     *
     * @param $transactionID
     * @param $entranceCode
     * @return void
     */
    public function PaymentStatus($transactionID, $entranceCode)
    {

        $r = new PaymentStatusBluemRequest(
            $this->_config,
            $transactionID,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : ""),
            $entranceCode
        );

        $response = $this->PerformRequest($r);
        return $response;
    }

    /**
     * Create a payment Transaction ID in the required structure, based on the order ID, customer ID and the current timestamp.
     * @param String $debtorReference
     */
    public function CreatePaymentTransactionID(String $debtorReference): String
    {
        return substr($debtorReference, 0, 28) . Carbon::now()->format('Ymd');
    }



    /**-------------- IDENTITY SPECIFIC FUNCTIONS --------------*/
    /**
     * Create Identity request based on a category, description, reference and given a return URL
     *
     * @param [type] $requestCategory
     * @param [type] $description
     * @param [type] $debtorReference
     * @param [type] $debtorReturnURL
     * @return IdentityBluemRequest
     */
    public function CreateIdentityRequest(
        $requestCategory,
        string $description,
        $debtorReference,
        $debtorReturnURL,
        $entranceCode = ""
    ): IdentityBluemRequest {

        $r = new IdentityBluemRequest(
            $this->_config,
            $entranceCode,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : ""),
            $requestCategory,
            $description,
            $debtorReference,
            $debtorReturnURL
        );
        //$this->CreateIdentityTransactionID($debtorReference),
        return $r;
    }

    /**
     * Retrieve Identity request status
     *
     * @param [type] $transactionID
     * @param [type] $entranceCode
     * @return void
     */
    public function IdentityStatus($transactionID, $entranceCode)
    {

        $r = new IdentityStatusBluemRequest(
            $this->_config,
            $entranceCode,
            ($this->_config->environment == BLUEM_ENVIRONMENT_TESTING &&
                isset($this->_config->expected_return) ?
                $this->_config->expected_return : ""),
            $transactionID
        );

        $response = $this->PerformRequest($r);
        return $response;
    }

    /**
     * Create a Identity Transaction ID in the required structure, based on the order ID, customer ID and the current timestamp.
     * @param String $debtorReference
     * @return String Identity Transaction ID
     */
    public function CreateIdentityTransactionID(String $debtorReference): String
    {
        return substr($debtorReference, 0, 28) . Carbon::now()->format('Ymd');
    }


    /** Universal Functions */
    /**
     * Generate an entrance code based on the current date and time.
     */
    public function CreateEntranceCode(): String
    {
        return Carbon::now()->format("YmdHisv"); // . "000";
    }

    /**
     * Perform a request to the BlueM API given a request
     * object and return its response
     *
     * @param  BluemRequest $transaction_request The Request Object
     *
     * @return ErrorBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse|IdentityTransactionBluemResponse|IdentityStatusBluemResponse|IBANNameCheckBluemResponse|Exception
     */
    public function PerformRequest(BluemRequest $transaction_request)
    {

        // set this to true if you want more internal information when debugging or extending
        $verbose = false;

        // make sure the timezone is set correctly..
        $now = Carbon::now()->timezone('Europe/Amsterdam');

        $xttrs_filename = $transaction_request->transaction_code . "-{$this->_config->senderID}-BSP1-" . $now->format('YmdHis') . "000.xml";

        // conform Rfc1123 standard in GMT time
        $xttrs_date = $now->toRfc7231String();

        $request_url = $transaction_request->HttpRequestUrl();

        $req = new BluemHttpRequest();

        $req->setUrl($request_url);
        $req->setMethod(BluemHttpRequest::METHOD_POST);

        $req->setHeader('Access-Control-Allow-Origin', '*');
        $req->setHeader("Content-Type", "application/xml; type=" . $transaction_request->transaction_code . "; charset=UTF-8");
        $req->setHeader("x-ttrs-date", $xttrs_date);
        $req->setHeader("x-ttrs-files-count", "1");
        $req->setHeader("x-ttrs-filename", $xttrs_filename);

        if ($verbose) {
            echo PHP_EOL . "<BR>URL// " . $request_url;

            echo PHP_EOL . "<BR>HEADER// " . "Content-Type: " . "application/xml; type=" . $transaction_request->transaction_code . "; charset=UTF-8";
            echo PHP_EOL . "<BR>HEADER// " . 'x-ttrs-date: ' . $xttrs_date;
            echo PHP_EOL . "<BR>HEADER// " . 'x-ttrs-files-count: ' . '1';
            echo PHP_EOL . "<BR>HEADER// " . 'x-ttrs-filename: ' . $xttrs_filename;
            echo "<HR>";
            echo PHP_EOL . "BODY: " . $transaction_request->XmlString();
            // var_dump(libxml_get_errors());
        }

        $req->setBody($transaction_request->XmlString());
        try {
            $http_response = $req->send();
            if ($verbose) {
                echo PHP_EOL . "<BR>RESPONSE// ";
                var_dump($http_response->getBody());
            }

            switch ($http_response->getStatus()) {
                case 200: {
                        if ($http_response->getBody() == "") {
                            return new ErrorBluemResponse("Error: Empty response returned");
                        }

                        try {
                            $response = $this->fabricateResponseObject($transaction_request->transaction_code, $http_response->getBody());
                        } catch (\Throwable $th) {
                            return new ErrorBluemResponse("Error: Could not create Bluem Response object. More details: " . $th->getMessage());
                        }
                        if (!$response->Status()) {
                            return new ErrorBluemResponse("Error: " . ($response->Error->ErrorMessage));
                        }
                        return $response;

                        break;
                    }
                case 400: {
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
        } catch (Throwable $e) {
            $error = new ErrorBluemResponse('HTTP Request Error');
            return $error;
        }
    }


    /** Webhook Code
     *
     * Senders provide Bluem with a webhook URL.
     * The URL will be checked for consistency and
     * validity and will not be stored if any of the
     * checks fails. */

    /**
     * Webhook for BlueM Mandate signature verification procedure
     */
    public function Webhook()
    {
        // set this to true if you want more internal information when debugging or extending
        $verbose = false;

        // The following checks will be performed:
        // @todo URL must start with https://


        // Check: ONLY Accept post requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($verbose) {
                exit("Not post");
            }
            http_response_code(400);
            exit();
        }

        // Check: An empty POST to the URL (normal HTTP request) always has to respond with HTTP 200 OK
        $postData = file_get_contents('php://input');

        if ($postData === "") {
            if ($verbose) {
                echo "NO POST";
            }
            http_response_code(200);
            exit();
        }

        // Check: content type has to be: "Content-type", "text/xml; charset=UTF-8"

        // Parsing XML data from POST body
        try {
            $xmlObject = new \SimpleXMLElement($postData);
        } catch (Exception $e) {
            if ($verbose) {
                var_dump($e);
                exit();
            }
            http_response_code(400); // could not parse XML
            exit();
        }

        // Check: if signature is valid in postdata
        if (!$this->_validateWebhookSignature($postData)) {
            if ($verbose) {
                exit('no valid webhook sig');
            }

            http_response_code(400);
            // echo 'The XML signature is not valid.';
            // echo PHP_EOL;
            exit;
        }

        if ($verbose) {
            var_dump($xmlObject);
        }

        // @todo: finish this code
        throw new Exception("Not implemented fully yet, please contact the developer or work around this error");
        // @todo webhook response dependent on the interface, check the status update

        // @todo webhook response mandates

        // @todo webhook response payments
        if (!isset($xmlObject->EPaymentInterface->PaymentStatusUpdate)) {
            http_response_code(400);
            exit;
        }
        $status_update = $xmlObject->EPaymentInterface->PaymentStatusUpdate;
        return $status_update;

        // @todo webhook response identity

        // @todo webhook response and more

        // @todo catch exceptions
    }

    /**
     * Validate webhook signature based on a key file
     * available in the `keys` folder
     *
     * @param  $xmlInput
     * @return bool
     */
    private function _validateWebhookSignature($xmlInput): bool
    {
        $temp_file = tmpfile();
        fwrite($temp_file, $xmlInput);
        $temp_file_path = stream_get_meta_data($temp_file)['uri'];

        $signatureValidator = new XmlSignatureValidator();

        // @todo Check if keyfile has to be chosen according to env
        // if ($this->_config->environment === BLUEM_ENVIRONMENT_TESTING) {
        // $public_key_file = "webhook.bluem.nl_pub_cert_test.crt";
        // } else {
        // $public_key_file = "webhook.bluem.nl_pub_key_production.crt";
        // }
        $key_folder =
            $public_key_file = "bluem_nl.crt";
        $public_key_file_path = __DIR__ . "/../keys/" . $public_key_file;
        // TODO: put the key in a different folder, relative to this PHP library
        // echo $public_key_file_path;
        // die();

        try {
            $signatureValidator->loadPublicKeyFile($public_key_file_path);
        } catch (\Throwable $th) {
            return false;
            // echo "Error: " . $th->getMessage();

        }

        $isValid = $signatureValidator->verifyXmlFile($temp_file_path);
        fclose($temp_file);

        if ($isValid) {
            return true;
        }
        return false;
    }

    /**
     * Create the proper response object class
     *
     * @param [type] $type
     * @param [type] $response_xml
     * @return MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse|IdentityTransactionBluemResponse|IdentityStatusBluemResponse|IBANNameCheckBluemResponse|Exception
     */
    private function fabricateResponseObject($type, $response_xml)
    {
        switch ($type) {
        case 'SRX':
        case 'SUD':
            return new MandateStatusBluemResponse($response_xml);
        case 'TRX':
        case 'TRS':
            return new MandateTransactionBluemResponse($response_xml);
        case 'PSU':
        case 'PSX':
            return new PaymentStatusBluemResponse($response_xml);
        case 'PTS':
        case 'PTX':
            return new PaymentTransactionBluemResponse($response_xml);
        case 'ITX':
        case 'ITX':
            return new IdentityTransactionBluemResponse($response_xml);
        case 'ISU':
        case 'ISX':
            return new IdentityStatusBluemResponse($response_xml);
        case 'INS':
        case 'INX':
            return new IBANNameCheckBluemResponse($response_xml);
        default:
            throw new Exception("Invalid transaction type requested");
        }
    }


    /**
     * Retrieve a list of all possible identity request types, which can be useful for reference
     *
     * @return void
     */
    public function GetIdentityRequestTypes() {
        return [
            "CustomerIDRequest",
            "NameRequest",
            "AddressRequest",
            "BirthDateRequest",
            "AgeCheckRequest",
            "GenderRequest",
            "TelephoneRequest",
            "EmailRequest"
        ];
    }



    /* IBAN SPECIFIC */


    public function CreateIBANNameCheckRequest($iban,$name,$debtorReference="") {

        $entranceCode = $this->CreateEntranceCode();
// var_dump($iban);
// die();
        $request = new IbanBluemRequest($this->_config,$entranceCode,$iban,$name,$debtorReference);
        return $request;
    }

    public function IBANNameCheck($iban,$name,$debtorReference="") {
        $r = $this->CreateIBANNameCheckRequest($iban,$name,$debtorReference);
        $response = $this->PerformRequest($r);
        return $response;
    }




    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @return array
     */
    public function retrieveBICCodesForContext($contextName)
    {
        $context = $this->_retrieveContext($contextName);
        return $context->getBICCodes();
    }

    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @return array
     */
    public function retrieveBICsForContext($contextName)
    {
        $context = $this->_retrieveContext($contextName);
        return $context->getBICs();
    }

    public function _retrieveContext($context)
    {
        $localInstrumentCode = $this->_config->localInstrumentCode;
        switch ($context) {
        case 'Mandates':
            $context = new MandatesContext($localInstrumentCode);
            break;
        case 'Payments':
            $context = new PaymentsContext();
            break;
        case 'Identity':
            $context = new IdentityContext();
            break;
        default:
            $contexts = ["Mandates","Payments","Identity"];
            throw new Exception(
                "Invalid Context requested, should be
                one of the following: ".
                implode(",",$contexts)
            );
            break;
        }
        return $context;
    }

}
