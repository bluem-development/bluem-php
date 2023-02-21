<?php

namespace Bluem\BluemPHP\Validators;

use Selective\XmlDSig\Exception\XmlSignatureValidatorException;
use Selective\XmlDSig\XmlSignatureValidator;
use Throwable;

// @todo: add signature validator tests
class WebhookSignatureValidation extends WebhookValidator
{
    private const KEY_NAME = 'bluem_nl.crt';
    private const KEY_FOLDER = __DIR__ . "/../../keys/";
    private const KEY_PATH = self::KEY_FOLDER . self::KEY_NAME;

    /**
     * Validate webhook signature based on a key file
     * available in the `keys` folder.
     */
    public function validate(string $postData): self {
        
        $temp_file = tmpfile();
        fwrite( $temp_file, $postData );
        $temp_file_path = stream_get_meta_data( $temp_file )['uri'];
        
        $signatureValidator = new XmlSignatureValidator();
        $public_key_file_path = self::KEY_PATH;

        try {
            $signatureValidator->loadPublicKeyFile( $public_key_file_path );
        } catch ( Throwable) {
            $this->addError("Couldn't load public key file");
        }

        $xmlVerified = $signatureValidator->verifyXmlFile( $temp_file_path );
        if ( ! $xmlVerified ) {
            $this->addError("Invalid signature");
        }
        fclose( $temp_file );

        return $this;
    }
}