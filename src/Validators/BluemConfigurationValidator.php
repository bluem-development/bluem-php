<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Exception;
use Throwable;

define("BLUEM_EXPECTED_RETURN_NONE", "none");
define("BLUEM_EXPECTED_RETURN_SUCCESS", "success");
define("BLUEM_EXPECTED_RETURN_CANCELLED", "cancelled");
define("BLUEM_EXPECTED_RETURN_EXPIRED", "expired");
define("BLUEM_EXPECTED_RETURN_FAILURE", "failure");
define("BLUEM_EXPECTED_RETURN_OPEN", "open");
define("BLUEM_EXPECTED_RETURN_PENDING", "pending");


class BluemConfigurationValidator
{
    private ?array $errors = null;

    function validate($config)
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

        } catch (Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }

        return $config;
    }

    private function _validateEnvironment($config)
    {
        if (!in_array(
            $config->environment,
            [
            BLUEM_ENVIRONMENT_TESTING,
            BLUEM_ENVIRONMENT_ACCEPTANCE,
            BLUEM_ENVIRONMENT_PRODUCTION
            ],
            true
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
        if (!str_starts_with($config->senderID, "S")) {
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
        if ($config->environment === BLUEM_ENVIRONMENT_PRODUCTION
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
            throw new Exception("brandID not set; please add this to your configuration when instantiating the Bluem integration");
        }

        return $config;
    }

    private function _validateMerchantIDAndSelectAccessToken($config)
    {
        if (! isset($config->merchantId)) {
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
        // @todo consider throwing an exception if this url is missing.
        return $config;
    }


    /**
     * if an invalid possible return status is given, set it to a default value (for testing purposes only)
     *
     * @param $config
     *
     * @return mixed
     */
    private function _validateExpectedReturnStatus($config): mixed
    {
        if ($config->environment === BLUEM_ENVIRONMENT_TESTING) {
            if (! isset($config->expectedReturnStatus)
                || ( $config->expectedReturnStatus !== ""
                && !in_array($config->expectedReturnStatus, $this->getPossibleReturnStatuses(), true))
            ) {
                // default back to success
                $config->expectedReturnStatus = BLUEM_EXPECTED_RETURN_SUCCESS;
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
            BLUEM_EXPECTED_RETURN_NONE,
            BLUEM_EXPECTED_RETURN_SUCCESS,
            BLUEM_EXPECTED_RETURN_CANCELLED,
            BLUEM_EXPECTED_RETURN_EXPIRED,
            BLUEM_EXPECTED_RETURN_FAILURE,
            BLUEM_EXPECTED_RETURN_OPEN,
            BLUEM_EXPECTED_RETURN_PENDING
        ];
    }

    private function _validateEMandateReason($config)
    {
        // @todo Validate the reason for the mandate
        return $config;
    }

    private function _validateLocalInstrumentCode($config)
    {
        if (! isset($config->localInstrumentCode)
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
