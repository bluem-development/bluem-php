<?php

namespace Bluem\BluemPHP\Validators;

use Selective\XmlDSig\XmlSignatureValidator;
use Throwable;

class WebhookSignatureValidation extends WebhookValidator
{
    private string $postdata;

    public function __construct(string $xmlObject)
    {
        $this->postdata = $xmlObject;
    }


    /**
     * Validate webhook signature based on a key file
     * available in the `keys` folder
     *
     * @return WebhookSignatureValidation
     */
    public function validate(): WebhookSignatureValidation {
        $temp_file = tmpfile();
        fwrite( $temp_file, $this->postdata );
        $temp_file_path = stream_get_meta_data( $temp_file )['uri'];
        
        $signatureValidator = new XmlSignatureValidator();

        // @todo Check if keyfile has to be chosen according to env
        // if ($this->_config->environment === BLUEM_ENVIRONMENT_TESTING) {
        // $public_key_file = "webhook.bluem.nl_pub_cert_test.crt";
        // } else {
        // $public_key_file = "webhook.bluem.nl_pub_key_production.crt";
        // }
        $public_key_file_path = __DIR__ . "/../../keys/" . $this->getKeyFileName();
        
        try {
            $signatureValidator->loadPublicKeyFile( $public_key_file_path );
        } catch ( Throwable $th ) {
            $this->addError("Couldn't load public key file");
            // echo "Error: " . $th->getMessage();
        }

        $isValid = $signatureValidator->verifyXmlFile( $temp_file_path );
        fclose( $temp_file );
        if ( ! $isValid ) {
            $this->addError("Invalid signature");
        }

        return $this;
    }

    private function getKeyFileName(): string
    {
//        webhook_
        return 'bluem_nl.crt';
    }
}