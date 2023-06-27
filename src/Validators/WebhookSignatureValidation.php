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

    public function __construct(private string $env)
    {
    }

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
        
        $public_key_file_path = dirname(__DIR__, 2) . self::KEY_FOLDER . $this->getKeyFileName();

        try {
            $signatureValidator->loadPublicKeyFile( $public_key_file_path );
        } catch ( Exception $e ) {
            $this->addError($e->getMessage());
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
        // Define current date
        $current_date = Carbon::now()
            ->timezone('Europe/Amsterdam')
            ->format('Y-m-d');

        // Define current time
        $current_time = Carbon::now()
            ->timezone('Europe/Amsterdam')
            ->format('H:i');
        
        // Define the default filename
        $filename = 'webhook_bluem_nl_202206090200-202307110159';
        
        // Check the datetime for certificates
        if ( $this->env === 'test' && ( ( $current_date == "2023-06-28" && $current_time >= "08:00" ) || $current_date > "2023-06-28") ) {
            $filename = 'webhook_bluem_nl_202306140200-202407050159';
        } elseif ( $this->env === 'prod' && ( ( $current_date == "2023-07-04" && $current_time >= "08:00" ) || $current_date > "2023-07-04") ) {
            $filename = 'webhook_bluem_nl_202306140200-202407050159';
        }
        return $filename . '.pem';
    }
}