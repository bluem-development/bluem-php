<?php

namespace Bluem\BluemPHP\Validators;

use Exception;
use Throwable;

class BluemConfigurationValidator
{
    /** @var array */
    private $errors ;
    

    function validate($config) {
        
        // essential validation
        try {
            $config = $this->_validateEnvironment($config);
            $config = $this->_validateSenderID($config);
            $config = $this->_validateTest_accessToken($config);
            $config = $this->_validateProduction_accessToken($config);
            $config = $this->_validateBrandID($config);
    
            // secondary values, possibly automatically inferred
            $config = $this->_validateMerchantIDAndSelectAccessToken($config);
            $config = $this->_validateThanksPage($config);
            $config = $this->_validateExpectedReturnStatus($config);
            $config = $this->_validateEMandateReason($config);
            $config = $this->_validateLocalInstrumentCode($config);
            $config = $this->_validateMerchantReturnURLBase($config);
            
        } catch(Throwable $th) {
            $this->errors[] = $th->getMessage();
            return false;
        }
        return $config;
        
    }

    public function errors(): array
    {
        return $this->errors;
    }
    
    private function _validateEnvironment($config)
    {
        if (!in_array(
            $config->environment,
            [
                BLUEM_ENVIRONMENT_TESTING,
                BLUEM_ENVIRONMENT_ACCEPTANCE,
                BLUEM_ENVIRONMENT_PRODUCTION
            ]
        )
        ) {
            throw new Exception(
                "Invalid environment setting, should be either
                'test', 'acc' or 'prod'"
            );
        }
        return $config;
    }
    private function _validateSenderID($config)
    {
        if (!isset($config->senderID)) {
            throw new Exception(
                "senderID not set; 
                please add this to your configuration when instantiating the Bluem integration"
            );
        }
        if ($config->senderID =="") {
            throw new Exception(
                "senderID cannot be empty; 
                please add this to your configuration when instantiating the Bluem integration"
            );
        }
        if (substr($config->senderID, 0, 1) !== "S") {
            throw new Exception(
                "senderID always starts with an S followed by digits. 
                Please correct this in your configuration when instantiating the Bluem integration"
            );
        }

        return $config;
    }
    private function _validateTest_accessToken($config)
    {
        if ($config->environment === BLUEM_ENVIRONMENT_TESTING
            && (!isset($config->test_accessToken)
                || $config->test_accessToken ==="")
        ) {
            throw new Exception(
                "test_accessToken not set correctly; please add this 
                to your configuration when instantiating the Bluem integration"
            );
        }
        return $config;
    }
    private function _validateProduction_accessToken($config)
    {
        // only required if mode is set to PROD
        // production_accessToken
        if ($config->environment === BLUEM_ENVIRONMENT_PRODUCTION
            && (!isset($config->production_accessToken)
                || $config->production_accessToken ==="")
        ) {
            throw new Exception(
                "production_accessToken not set correctly; 
                please add this to your configuration when 
                instantiating the Bluem integration"
            );
        }
        return $config;
    }

    private function _validateMerchantIDAndSelectAccessToken($config)
    {
        if (!isset($config->merchantId)) {
            $config->merchantId = "";
        }

        if ($config->environment === BLUEM_ENVIRONMENT_PRODUCTION) {
            $config->accessToken = $config->production_accessToken;
            // @todo consider throwing an exception if these tokens are missing.
        } elseif ($config->environment === BLUEM_ENVIRONMENT_TESTING) {
            $config->accessToken = $config->test_accessToken;
            // @todo consider throwing an exception if these tokens are missing.

            // hardcoded merchantID in case of test.
            // It is always the bluem merchant ID then.
            $config->merchantID = BLUEM_STATIC_MERCHANT_ID;
        }
        return $config;
    }
    private function _validateThanksPage($config)
    {
        return $config;
    }
    private function _validateExpectedReturnStatus($config)
    {
        // expectedReturnStatus
        // if an invalid possible return status is given, set it to a default value (for testing purposes only)
        $possibleReturnStatuses = [
            "none",     "success",  "cancelled",
            "expired",  "failure",  "open",
            "pending"
        ];
        if ($config->expectedReturnStatus!==""
            && !in_array(
                $config->expectedReturnStatus,
                $possibleReturnStatuses
            )
        ) {
            $config->expectedReturnStatus = "success";
        }

        return $config;
    }
    private function _validateBrandID($config)
    {
        if (!isset($config->brandID)) {
            throw new Exception("brandID not set; please add this to your configuration when instantiating the Bluem integration");
        }
        return $config;
    }
    private function _validateEMandateReason($config)
    {
        return $config;
    }
    private function _validateLocalInstrumentCode($config)
    {
        if (!isset($config->localInstrumentCode)
            || !in_array(
                $config->localInstrumentCode,
                ['B2B', 'CORE']
            )
        ) {
            // defaulting localInstrumentCode
            $config->localInstrumentCode = "CORE";
        }
        return $config;
    }
    private function _validateMerchantReturnURLBase($config)
    {
        return $config;
    }
}