<?php

namespace Bluem\BluemPHP;

use Exception;
use SimpleXMLElement;

class Webhook
{

    private string $senderID;
    private bool $webhookDebugging;
    
    public function __construct(
        $senderID, $webhookDebugging = false
    )
    {
        $this->senderID = $senderID;
        $this->webhookDebugging = $webhookDebugging;
    }
    
    public function execute(): void
    {
        // Check: secure connection (if not in debug mode)
        if (!$this->isHttpsRequest()) {
            if ($this->webhookDebugging) {
                echo "Warning: not HTTPS" . PHP_EOL;
            } else {
                $this->exitWithError('Webhook request is not HTTPS');
            }
        }

        // Check: ONLY Accept post requests
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            $this->exitWithError('Validation error: Not post');
        }

        // Check: An empty POST to the URL (normal HTTP request) always has to respond with HTTP 200 OK.
        $postData = file_get_contents( 'php://input' );

        if ( $postData === "" ) {
            $this->exitWithError('Input error: no data body given');
        }

        // Check: content type: XML with utf-8 encoding
        if ($_SERVER["CONTENT_TYPE"] !== "text/xml; charset=UTF-8") {
            $this->exitWithError('Wrong Content-Type given; should be xml with UTF-8 encoding');
        }
        
        // Parsing XML data from POST body
        try {
            $xmlObject = new SimpleXMLElement( $postData );
        } catch ( Exception $e ) {
            if ( $this->webhookDebugging ) {
                echo( $e->getMessage() );
                exit();
            }
            http_response_code( 400 ); // could not parse XML
            exit();
        }

        // Check: XML data validates
        $xmlValidation = (new Validators\WebhookXmlValidation($xmlObject, $this->senderID))->validate();

        if(!$xmlValidation->isValid) {
            $this->exitWithError("Invalid input XML data given: " . implode(', ', $xmlValidation->errors));
        }

        // Check: if signature is valid in postdata
        $signatureValidation = (new Validators\WebhookSignatureValidation($postData))->validate();
        if ( ! $signatureValidation->isValid ) {
            $this->exitWithError('The XML signature is not valid: '. implode(', ', $signatureValidation->errors));
        }
        
        // ----
        echo "You have a valid webhook here!";
        // @todo webhook response dependent on the interface, check the status update

        // @todo webhook response mandates

        // @todo webhook response payments
//        if (!isset($xmlObject->EPaymentInterface->PaymentStatusUpdate)) {
//            http_response_code(400);
//            exit;
//        }
//        return $xmlObject->EPaymentInterface->PaymentStatusUpdate;

        // @todo webhook response identity

        // @todo webhook response and more

        // @todo catch exceptions
    }


   


    /**
     * @return bool
     */
    private function isHttpsRequest(): bool
    {
        return ((!empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] === 443
        );
    }

    private function exitWithError(string $string, int $errorCode = 400): void
    {
        http_response_code( $errorCode );
        if ($this->webhookDebugging) {
            exit($string);
        }
        exit;
    }
}