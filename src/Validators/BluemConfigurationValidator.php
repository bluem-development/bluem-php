<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Bluem\BluemPHP\Constants;
use Exception;
use RuntimeException;
use Throwable;

class BluemConfigurationValidator
{
    private ?array $errors = null;

    public function validate($config)
    {

        // essential validation
        try {
            $config = $this->_validateEnvironment($config);
            $config = $this->_validateSenderID($config);
            $config = $this->_validateTest_accessToken($config);
            $config = $this->_validateProduction_accessToken($config);
            $config = $this->_validateBrandID($config);

            // @todo: add validation for iDIN and eMandates and ePayments brandIDs if they are present

            // secondary values, possibly automatically inferred
            $config = $this->_validateMerchantIDAndSelectAccessToken($config);
            $config = $this->_validateThanksPage($config);
            $config = $this->_validateExpectedReturnStatus($config);
            $config = $this->_validateEMandateReason($config);
            $config = $this->_validateLocalInstrumentCode($config);
            $config = $this->_validateMerchantReturnURLBase($config);
        } catch (Throwable $throwable) {
            $this->errors[] = $throwable->getMessage();

            return false;
        }

        return $config;
    }

    private function _validateEnvironment($config)
    {
        if (
            !isset($config->environment)
        ) {
            throw new Exception(
                "environment not set; please add this to your configuration when instantiating the Bluem integration"
            );
        }

        if (
            !in_array(
                $config->environment,
                Constants::ENVIRONMENTS,
                true
            )
        ) {
            throw new Exception(
                sprintf(sprintf("Invalid environment setting (%s), should be one of: 
                %%s", $config->environment), implode(', ', Constants::ENVIRONMENTS))
            );
        }

        return $config;
    }

    private function _validateSenderID($config)
    {
        if (! isset($config->senderID)) {
            throw new Exception(
                "senderID not set; 
                please add this to your configuration when instantiating the Bluem integration"
            );
        }

        if ($config->senderID === "") {
            throw new Exception(
                "senderID cannot be empty; 
                please add this to your configuration when instantiating the Bluem integration"
            );
        }

        if (!str_starts_with((string) $config->senderID, "S")) {
            throw new Exception(
                "senderID always starts with an S followed by digits. 
                Please correct this in your configuration when instantiating the Bluem integration"
            );
        }

        return $config;
    }

    private function _validateTest_accessToken($config)
    {
        if (
            $config->environment === Constants::TESTING_ENVIRONMENT
            && ( ! isset($config->test_accessToken)
            || $config->test_accessToken === "" )
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
        if (
            $config->environment === Constants::PRODUCTION_ENVIRONMENT
            && ( ! isset($config->production_accessToken)
            || $config->production_accessToken === "" )
        ) {
            throw new Exception(
                "production_accessToken not set correctly; 
                please add this to your configuration when 
                instantiating the Bluem integration"
            );
        }

        return $config;
    }

    private function _validateBrandID($config)
    {
        if (! isset($config->brandID)) {
            throw new RuntimeException(
                "brandID not set; please add this to your configuration when instantiating the Bluem integration"
            );
        }

        return $config;
    }

    private function _validateMerchantIDAndSelectAccessToken($config)
    {
        if (! isset($config->merchantID)) {
            $config->merchantID = "";
        }

        if ($config->environment === Constants::PRODUCTION_ENVIRONMENT) {
            $config->accessToken = $config->production_accessToken;
            // @todo consider throwing an exception if these tokens are missing.
        } elseif ($config->environment === Constants::TESTING_ENVIRONMENT) {
            $config->accessToken = $config->test_accessToken;
            // @todo consider throwing an exception if these tokens are missing.

            // hardcoded merchantID in case of test.
            // It is always the bluem merchant ID then.
            $config->merchantID = Constants::BLUEM_STATIC_MERCHANT_ID;
        }

        return $config;
    }


    private function _validateThanksPage($config)
    {
        // @todo consider throwing an exception if this url is missing.
        return $config;
    }


    /**
     * if an invalid possible return status is given, set it to a default value (for testing purposes only)
     *
     * @param $config
     */
    private function _validateExpectedReturnStatus($config): mixed
    {
        if ($config->environment === Constants::TESTING_ENVIRONMENT) {
            if (
                ! isset($config->expectedReturnStatus)
                || ( $config->expectedReturnStatus !== ""
                && !in_array($config->expectedReturnStatus, $this->getPossibleReturnStatuses(), true))
            ) {
                // default back to success
                $config->expectedReturnStatus = Constants::EXPECTED_RETURN_SUCCESS;
            }
        } else {
            // no need for expectedReturnStatus when in production
            unset($config->expectedReturnStatus);
        }

        return $config;
    }

    /**
     * @return array List of possible return statuses as strings
     */
    private function getPossibleReturnStatuses(): array
    {
        return [
            Constants::EXPECTED_RETURN_NONE,
            Constants::EXPECTED_RETURN_SUCCESS,
            Constants::EXPECTED_RETURN_CANCELLED,
            Constants::EXPECTED_RETURN_EXPIRED,
            Constants::EXPECTED_RETURN_FAILURE,
            Constants::EXPECTED_RETURN_OPEN,
            Constants::EXPECTED_RETURN_PENDING
        ];
    }

    private function _validateEMandateReason($config)
    {
        // @todo Validate the reason for the mandate
        return $config;
    }

    private function _validateLocalInstrumentCode($config)
    {
        if (
            ! isset($config->localInstrumentCode)
            || ! in_array(
                $config->localInstrumentCode,
                [ 'B2B', 'CORE' ]
            )
        ) {
            // defaulting localInstrumentCode
            $config->localInstrumentCode = "CORE";
        }

        return $config;
    }

    private function _validateMerchantReturnURLBase($config)
    {
        // @todo Validate Merchant Return URL Base
        return $config;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
