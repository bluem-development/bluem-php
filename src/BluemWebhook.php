<?php

namespace Bluem\BluemPHP;

use Exception;
use Selective\XmlDSig\XmlSignatureValidator;
use SimpleXMLElement;
use Throwable;

class BluemWebhook
{

    private string $senderID;
    private bool $webhookDebugging = false;
    
    public function __construct(
        $senderID, $webhookDebugging = false
    )
    {
        $this->senderID = $senderID;
        $this->webhookDebugging = $webhookDebugging;
    }
    
    public function execute() {

        // The following checks will be performed:
        // Check: Must be secure connection (if not in debug mode)
        if (!$this->isHttpsRequest()) {
            if ($this->webhookDebugging) {
                echo "Warning: not HTTPS".PHP_EOL;
            } else {
                http_response_code( 400 );
                exit('Webhook request is not HTTPS');
            }
        }

        // Check: ONLY Accept post requests
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            http_response_code( 400 );
            if ( $this->webhookDebugging ) {
                exit( "Validation error: Not post" );
            }
            exit();
        }

        // Check: An empty POST to the URL (normal HTTP request) always has to respond with HTTP 200 OK
        $postData = file_get_contents( 'php://input' );

        if ( $postData === "" ) {
            if ( $this->webhookDebugging ) {
                exit("Input error: no data body given");
            }
            http_response_code( 200 );
            exit();
        }

        // Check: content type has to be: "Content-type", "text/xml; charset=UTF-8"

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

        // Check: XML data has to be a valid XML structure
        $xmlValidation = (new Validators\WebhookXmlValidation($xmlObject, $this->senderID))->validate();

        if(!$xmlValidation->isValid) {
            http_response_code(400);
            if($this->webhookDebugging) {
                exit("Invalid input XML data given: " . implode(', ', $xmlValidation->errors));
            }
            exit;
        }

        // Check: if signature is valid in postdata
        $signatureValidation = (new Validators\WebhookSignatureValidation($postData))->validate();
        if ( ! $signatureValidation->isValid ) {
            http_response_code( 400 );
            if ( $this->webhookDebugging ) {
                exit( 'The XML signature is not valid: '. implode(', ', $signatureValidation->errors) );
            }
            // echo 'The XML signature is not valid.';
            // echo PHP_EOL;
            exit;
        }

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
}