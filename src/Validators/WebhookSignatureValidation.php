<?php

namespace Bluem\BluemPHP\Validators;

use Selective\XmlDSig\Exception\XmlSignatureValidatorException;
use Selective\XmlDSig\XmlSignatureValidator;
use Carbon\Carbon;
use Throwable;

// @todo: add signature validator tests
class WebhookSignatureValidation extends WebhookValidator
{
    private const KEY_FOLDER = "/keys/";

    /**
     * Validate webhook signature based on a key file
     * available in the `keys` folder.
     */
    public function validate(string $postData): self
    {    
        $temp_file = tmpfile();
        fwrite( $temp_file, $postData );
        $temp_file_path = stream_get_meta_data( $temp_file )['uri'];
        
        $signatureValidator = new XmlSignatureValidator();
        
        $public_key_file_path = dirname(dirname(__DIR__)) . self::KEY_FOLDER . $this->getKeyFileName();

        try {
            $signatureValidator->loadPublicKeyFile( $public_key_file_path );
        } catch ( Throwable ) {
            $this->addError("Couldn't load public key file");
        }

        $xmlVerified = $signatureValidator->verifyXmlFile( $temp_file_path );
        if ( ! $xmlVerified ) {
            $this->addError("Invalid signature");
        }
        fclose( $temp_file );

        return $this;
    }

    /**
     * Determine filename certificate
     */
    private function getKeyFileName(): string
    {
        // Define current datetime using Carbon
        $current_datetime = Carbon::now()
            ->timezone('Europe/Amsterdam')
            ->format('Y-m-d');
        
        // Define the default filename
        $filename = 'webhook_bluem_nl_202206090200-202307110159';
        
        // Check the datetime for certificates
        if ( $current_datetime >= "2023-06-28" ) {
            $filename = 'webhook_bluem_nl_202306140200-202407050159';
        }
        return $filename . '.crt';
    }
}