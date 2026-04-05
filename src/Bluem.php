<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Bluem\BluemPHP\Contexts\IdentityContext;
use Bluem\BluemPHP\Contexts\MandatesContext;
use Bluem\BluemPHP\Contexts\PaymentsContext;
use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use Bluem\BluemPHP\Extensions\IPAPI;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use Bluem\BluemPHP\Helpers\Now;
use Bluem\BluemPHP\Interfaces\BluemContextInterface;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Bluem\BluemPHP\Interfaces\BluemResponseInterface;
use Bluem\BluemPHP\Requests\EmandateBluemRequest;
use Bluem\BluemPHP\Requests\EmandateStatusBluemRequest;
use Bluem\BluemPHP\Requests\IBANBluemRequest;
use Bluem\BluemPHP\Requests\IdentityBluemRequest;
use Bluem\BluemPHP\Requests\IdentityStatusBluemRequest;
use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Requests\PaymentStatusBluemRequest;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;
use Bluem\BluemPHP\Responses\IBANNameCheckBluemResponse;
use Bluem\BluemPHP\Responses\IdentityStatusBluemResponse;
use Bluem\BluemPHP\Responses\IdentityTransactionBluemResponse;
use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;
use Bluem\BluemPHP\Responses\MandateTransactionBluemResponse;
use Bluem\BluemPHP\Responses\PaymentStatusBluemResponse;
use Bluem\BluemPHP\Responses\PaymentTransactionBluemResponse;
use Bluem\BluemPHP\Transport\CurlHttpTransport;
use Bluem\BluemPHP\Transport\HttpTransportInterface;
use Bluem\BluemPHP\Validators\BluemXMLValidator;
use DOMException;
use Exception;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

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
    define("BLUEM_STATIC_MERCHANT_ID", "0020000387");
}

/**
 * Bluem Integration main class
 */
class Bluem
{
    /** @var bool Used for development and debugging purposes. */
    private static bool $verbose = false;

    public string $environment;

    private BluemConfiguration $configuration;

    private HttpTransportInterface $transport;


    /**
     * Bluem constructor.
     * Give a mixed configuration object to initialize the Bluem object
     *
     *
     * @throws InvalidBluemConfigurationException
     */
    public function __construct(mixed $rawConfig, ?HttpTransportInterface $transport = null)
    {
        if ($rawConfig === null) {
            throw new InvalidBluemConfigurationException('No configuration given');
        }

        try {
            $this->configuration = new BluemConfiguration($rawConfig);
        } catch (Exception $exception) {
            throw new InvalidBluemConfigurationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->transport = $transport ?? new CurlHttpTransport();
    }


    /**
     * @throws Exception
     */
    public function setConfig(string $key, $value): bool
    {

        if (! isset($this->configuration->$key)) {
            throw new RuntimeException(sprintf("Key '%s' does not exist in configuration", $key));
        }

        $this->configuration->$key = $value;

        return true;
    }

    /**
     * @return false|mixed
     */
    public function getConfig(string $key)
    {
        return $this->configuration->$key ?? false;
    }

    /**
     * Create a Mandate Request given a customer ID, order ID
     * and Mandate ID and return the request object,
     * sending it and returning the response
     *
     *
     * @throws Exception
     */
    public function Mandate(
        string $customer_id,
        string $order_id,
        string $mandate_id = ""
    ): BluemResponseInterface {
        try {
            $_request = $this->CreateMandateRequest(
                $customer_id,
                $order_id,
                $mandate_id
            );
        } catch (Exception $exception) {
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->PerformRequest($_request);
    }

    /**
     * Create a Mandate Request given a customer ID, order ID
     * and Mandate ID and return the request object
     * WITHOUT sending it
     *
     *
     * @throws Exception
     */
    public function CreateMandateRequest(
        string $customer_id,
        string $order_id,
        string $mandate_id = ""
    ): EmandateBluemRequest {
        // @todo add proper validation on customer or order ID via datatypes
        if ($customer_id === "") {
            throw new RuntimeException("Customer ID Not set", 1);
        }

        if ($order_id === "") {
            throw new RuntimeException("Order ID Not set", 1);
        }

        if ($mandate_id === "") {
            $mandate_id = $this->CreateMandateID($order_id, $customer_id);
        }

        return new EmandateBluemRequest(
            $this->configuration,
            $customer_id,
            $order_id,
            $mandate_id,
            ( $this->configuration->environment === "test" &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "" )
        );
    }

    /**
     * Create a mandate ID in the required structure, based on the order ID, customer ID and the current timestamp.
     *
     * @param String $order_id The order ID
     * @param String $customer_id The customer ID
     */
    public function CreateMandateID(string $order_id, string $customer_id): string
    {
        $now = new Now();
        // veteranen search team, specific
        if ($this->configuration->senderID === "S1300") {
            return "M" . $now->format('YmdHis');
        }

        // For customer NextDeli et al
        return substr($customer_id . $now->format('Ymd') . $order_id, 0, 35);
    }

    /**
     * Perform a request to the Bluem API given a request
     * object and return its response
     *
     *
     * @throws DOMException
     * @throws Exception
     */
    public function PerformRequest(BluemRequestInterface $transaction_request): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse
    {
        $validator = new BluemXMLValidator();
        if (
            ! $validator->validate(
                $transaction_request->RequestContext(),
                $transaction_request->XmlString()
            )
        ) {
            return new ErrorBluemResponse(
                "Error: Request is not formed correctly. More details: " .
                implode(
                    ';<BR>' . PHP_EOL,
                    $validator->errorDetails
                )
            );
        }

        $now = new Now('UTC');
        // set timezone to UTC to let the transaction xttrs timestamp work; 8-9-2021

        $xttrs_filename = $transaction_request->transaction_code . sprintf('-%s-BSP1-', $this->configuration->senderID) . $now->format('YmdHis') . "000.xml";

        // conform Rfc1123 standard in GMT time
        // Since v2.0.5 : use preset format instead of
        // function to allow for Carbon 1.21 legacy compatibility
        $xttrs_date = $now->rfc1123();

        $request_url = $transaction_request->HttpRequestURL();

        $curl_xml = $transaction_request->XmlString();

        $headers = [
            'Access-Control-Allow-Origin: *',
            'Content-Type: application/xml; type=' . $transaction_request->transaction_code . '; charset=UTF-8',
            'x-ttrs-date: ' . $xttrs_date,
            'x-ttrs-files-count: 1',
            'x-ttrs-filename: ' . $xttrs_filename,
        ];

        try {
            $transport_response = $this->transport->send(
                url: $request_url,
                headers: $headers,
                body: "xmlRequest=" . $curl_xml
            );

            $response_status = $transport_response->statusCode;
            $responseBody = $transport_response->body;

            switch ($response_status) {
                case 200:
                    if ($responseBody === '' || $responseBody === '0') {
                        return new ErrorBluemResponse("Error: Empty response returned");
                    }
                    $xml = $this->parseResponseXml($responseBody);
                    if ($xml === null) {
                        return new ErrorBluemResponse('Error: Could not parse Bluem response XML');
                    }
                    try {
                        $bluemResponse = $this->fabricateResponseObject($transaction_request->transaction_code, $responseBody);
                    } catch (Throwable $th) {
                        return new ErrorBluemResponse("Error: Could not create Bluem Response object. More details: " . $th->getMessage());
                    }
                    $rootAttributes = $xml->attributes();
                    if ($rootAttributes !== null && isset($rootAttributes['type']) && (string) $rootAttributes['type'] === 'ErrorResponse') {
                        $errorMessage = $this->extractErrorMessage($xml, (string) $transaction_request->transaction_code);

                        // @todo: move into a separate function
                        return new ErrorBluemResponse("Error: " . ( $errorMessage ));
                    }
                    if (! $bluemResponse->Status()) {
                        return new ErrorBluemResponse("Error: " . $bluemResponse->Error());
                    }
                    return $bluemResponse;
                case 400:
                    return new ErrorBluemResponse('Your request was not formed correctly.');
                case 401:
                    return new ErrorBluemResponse('Unauthorized: check your access credentials.');
                case 500:
                    return new ErrorBluemResponse('An unrecoverable error at the server side occurred while processing the request');
                default:
                    return new ErrorBluemResponse('Unexpected / erroneous response (code ' . $response_status . ')');
            }
        } catch (Throwable $throwable) {
            return new ErrorBluemResponse('HTTP Request Error' . $throwable->getMessage());
            // @todo improve request return exceptions; add our own exception type
        }
    }

    private function parseResponseXml(string $response): ?SimpleXMLElement
    {
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        if ($xml === false) {
            return null;
        }

        return $xml;
    }

    private function extractErrorMessage(SimpleXMLElement $xml, string $transactionCode): string
    {
        $errorNodeName = match ($transactionCode) {
            'SRX', 'SUD', 'TRX', 'TRS' => 'EMandateErrorResponse',
            'PSU', 'PSX', 'PTS', 'PTX' => 'PaymentErrorResponse',
            'ITS', 'ITX', 'ISU', 'ISX' => 'IdentityErrorResponse',
            'INS', 'INX' => 'IBANCheckErrorResponse',
            default => throw new RuntimeException('Invalid transaction type requested'),
        };

        if (!isset($xml->{$errorNodeName}->Error->ErrorMessage)) {
            return '';
        }

        return (string) $xml->{$errorNodeName}->Error->ErrorMessage;
    }

    /**
     * Create the proper response object class
     *
     * @param $type
     * @param $response_xml
     *
     * @throws Exception
     */
    private function fabricateResponseObject($type, $response_xml): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse
    {
        return match ($type) {
            'SRX', 'SUD' => new MandateStatusBluemResponse($response_xml),
            'TRX', 'TRS' => new MandateTransactionBluemResponse($response_xml),
            'PSU', 'PSX' => new PaymentStatusBluemResponse($response_xml),
            'PTS', 'PTX' => new PaymentTransactionBluemResponse($response_xml),
            'ITS', 'ITX' => new IdentityTransactionBluemResponse($response_xml),
            'ISU', 'ISX' => new IdentityStatusBluemResponse($response_xml),
            'INS', 'INX' => new IBANNameCheckBluemResponse($response_xml),
            default => throw new RuntimeException("Invalid transaction type requested"),
        };
    }


    /**-------------- PAYMENT SPECIFIC FUNCTIONS --------------*/
    /**
     * Retrieving a mandate request's status based on a mandate ID and an entrance Code, and returning the response
     *
     * @param $mandateID
     * @param $entranceCode
     *
     * @throws DOMException
     */
    public function MandateStatus($mandateID, $entranceCode): BluemResponseInterface
    {
        $r = new EMandateStatusBluemRequest(
            $this->configuration,
            $mandateID,
            $entranceCode,
            ( $this->configuration->environment === BLUEM_ENVIRONMENT_TESTING &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "" )
        );

        return $this->PerformRequest($r);
    }

    /**
     * For mandates only: retrieve the maximum amount from
     * the AcceptanceReport to use in parsing and validating
     * mandates in webshop context
     *
     * @param $response
     */
    public function GetMaximumAmountFromTransactionResponse($response): object
    {
        return $response->getMaximumAmount();
    }

    /**
     * Create a payment request and perform it, returning the response
     *
     * @param        $debtorReference
     * @param        $amount
     *
     * @throws DOMException
     * @throws RuntimeException
     */
    public function Payment(
        string $description,
        $debtorReference,
        $amount,
        $dueDateTime = null,
        string $currency = "EUR",
        $entranceCode = null
    ): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse {
        try {
            $request = $this->CreatePaymentRequest(
                $description,
                $debtorReference,
                $amount,
                $dueDateTime,
                $currency,
                $entranceCode ??  $this->CreateEntranceCode()
            );
        } catch (Exception $exception) {
            throw new RuntimeException("Could not create request: " . $exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->PerformRequest($request);
    }

    /**
     * Generate an entrance code based on the current date and time.
     */
    public function CreateEntranceCode(): string
    {
        return (new Now())->format("YmdHisv"); // . "000";
    }

    // @todo: fix issue [RFC4](https://github.com/DaanRijpkema/bluem-php/issues/4)
    // When you create a PaymentBluemRequest, a $transactionID is generated (CreatePaymentTransactionID).
    // But that TransactionID doesn't make any sense because Bluem generates its own transactionID.
    // So the self generated TransactionID is added (together with the entranceCode) to the debtorReturnURL
    //  but on the return page you can't do anything with the transactionID because it doesn't match the transactionID known by Bluem.
    /**-------------- IDENTITY SPECIFIC FUNCTIONS --------------*/
    /**
     * Create a payment request object
     *
     * @param        $debtorReference
     *
     * @throws Exception
     */
    public function CreatePaymentRequest(
        string $description,
        $debtorReference,
        float $amount,
        $dueDateTime = null,
        string $currency = "EUR",
        $entranceCode = null,
        string $debtorReturnURL = "",
        string $paymentReference = ""
    ): PaymentBluemRequest {
        if (is_null($entranceCode)) {
            $entranceCode = $this->CreateEntranceCode();
        }

        // create try catch for these validation steps
        // @todo: validate Description
        // @todo: validate Amount
        // @todo: validate Currency
        // @todo: Create constants for Currencies
        // @todo: sanitize debtorReturnURL

        return new PaymentBluemRequest(
            $this->configuration,
            $description,
            $debtorReference,
            $amount,
            $dueDateTime,
            $currency,
            $this->CreatePaymentTransactionID($debtorReference),
            $entranceCode,
            ( $this->configuration->environment === BLUEM_ENVIRONMENT_TESTING &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "" ),
            $debtorReturnURL,
            $paymentReference
        );
    }

    /**
     * Create a payment Transaction ID in the required structure, based on the order ID, customer ID and the current timestamp.
     *
     *
     */
    public function CreatePaymentTransactionID(string $debtorReference): string
    {
        return substr($debtorReference, 0, 28) . (new Now())->format('Ymd');
    }


    // @todo: Create Identity shorthand function
    /**
     * Retrieve the status of a payment request, based on transactionID and Entrance Code
     *
     * @param $transactionID
     * @param $entranceCode
     *
     * @throws Exception
     */
    public function PaymentStatus($transactionID, $entranceCode): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse
    {
        $r = new PaymentStatusBluemRequest(
            $this->configuration,
            $transactionID,
            ( $this->configuration->environment === BLUEM_ENVIRONMENT_TESTING &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "" ),
            $entranceCode
        );

        return $this->PerformRequest($r);
    }


    /** Universal Functions */
    /**
     * Create Identity request based on a category, description, reference and given a return URL.
     *
     * @param        $requestCategory
     * @param        $debtorReference
     *
     * @throws Exception
     */
    public function CreateIdentityRequest(
        $requestCategory,
        string $description,
        $debtorReference,
        string $entranceCode = "",
        string $returnURL = ""
    ): IdentityBluemRequest {
        // todo: Check if this is needed?
        //$this->CreateIdentityTransactionID($debtorReference),

        return new IdentityBluemRequest(
            $this->configuration,
            $entranceCode,
            $this->configuration->environment === BLUEM_ENVIRONMENT_TESTING &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "",
            $requestCategory,
            $description,
            $debtorReference,
            $returnURL
        );
    }

    /**
     * Retrieve Identity request status
     *
     * @param $transactionID
     * @param $entranceCode
     *
     * @throws Exception
     */
    public function IdentityStatus($transactionID, $entranceCode): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse
    {
        $r = new IdentityStatusBluemRequest(
            $this->configuration,
            $entranceCode,
            ( $this->configuration->environment === BLUEM_ENVIRONMENT_TESTING &&
              $this->configuration->expectedReturnStatus !== null ?
                $this->configuration->expectedReturnStatus : "" ),
            $transactionID
        );

        return $this->PerformRequest($r);
    }


    /** Webhook Code
     *
     * Senders provide Bluem with a webhook URL.
     * The URL will be checked for consistency and
     * validity and will not be stored if any of the
     * checks fails. */
    /**
     * Create an Identity Transaction ID in the required structure, based on the order ID, customer ID and the current timestamp.
     *
     *
     * @return String Identity Transaction ID
     */
    public function CreateIdentityTransactionID(string $debtorReference): string
    {
        return substr($debtorReference, 0, 28) . (new Now())->format('Ymd');
    }

    /**
     * Webhook for Bluem Mandate signature verification procedure
     * Returns null if the webhook failed to be parsed
     * @returns null|PaymentStatusBluemResponse|MandateStatusBluemResponse|IdentityStatusBluemResponse
     */
    public function Webhook($data = '')
    {
        try {
            $webhook = new Webhook(
                $this->configuration->senderID,
                $this->configuration->environment,
                $data
            );
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

        return $webhook;
    }

    /**
     * Retrieve a list of all possible identity request types
     *
     * @return string[]
     */
    public function GetIdentityRequestTypes(): array
    {
        return [
            "CustomerIDRequest",
            "CustomerIDLoginRequest",
            "NameRequest",
            "AddressRequest",
            "BirthDateRequest",
            "AgeCheckRequest",
            "GenderRequest",
            "TelephoneRequest",
            "EmailRequest",
        ];
    }


    /* IBAN SPECIFIC */
    /**
     * Create and perform IBAN Name Check request
     * @param string $iban Given IBAN to check
     * @param string $name Given name to check
     * @param string $debtorReference An optional given debtor reference
     *                                to append to the check request
     * @throws DOMException
     * @throws Exception
     */
    public function IBANNameCheck(string $iban, string $name, string $debtorReference = ""): ErrorBluemResponse|IBANNameCheckBluemResponse|IdentityStatusBluemResponse|IdentityTransactionBluemResponse|MandateStatusBluemResponse|MandateTransactionBluemResponse|PaymentStatusBluemResponse|PaymentTransactionBluemResponse
    {
        $r = $this->CreateIBANNameCheckRequest($iban, $name, $debtorReference);

        return $this->PerformRequest($r);
    }

    /**
     * Create IBAN Name Check request
     *
     * @param string $iban Given IBAN to check
     * @param string $name Given name to check
     * @param string $debtorReference An optional given debtor reference
     *                                to append to the check request
     *
     * @throws Exception
     */
    public function CreateIBANNameCheckRequest(string $iban, string $name, string $debtorReference = ""): IBANBluemRequest
    {
        $entranceCode = $this->CreateEntranceCode();

        return new IBANBluemRequest(
            $this->configuration,
            $entranceCode,
            $iban,
            $name,
            $debtorReference
        );
    }

    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @param $contextName
     *
     * @throws Exception
     */
    public function retrieveBICCodesForContext($contextName): array
    {
        return $this->_retrieveContext($contextName)->getBICCodes();
    }

    /**
     * @param $context
     *
     * @return IdentityContext|MandatesContext|PaymentsContext
     * @throws Exception
     */
    public function _retrieveContext($context): BluemContextInterface
    {
        $localInstrumentCode = $this->configuration->localInstrumentCode;
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
                $contexts = [ "Mandates", "Payments", "Identity" ];
                throw new RuntimeException(
                    "Invalid Context requested, should be
                one of the following: " .
                    implode(",", $contexts)
                );
        }

        return $context;
    }

    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @param $contextName
     *
     * @throws Exception
     */
    public function retrieveBICsForContext($contextName): array
    {
        return $this->_retrieveContext($contextName)->getBICs();
    }

    /**
     * Verify if the current IP is based in the Netherlands
     * utilizing a geolocation integration.
     */
    public function VerifyIPIsNetherlands(): bool
    {
        try {
            return (new IPAPI())->CheckIsNetherlands();
        } catch (Throwable) {
            return false;
        }
    }
}
