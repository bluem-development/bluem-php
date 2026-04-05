<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Bluem\BluemPHP\Constants;
use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use Exception;
use RuntimeException;
use Throwable;

class BluemConfigurationValidator
{
    private ?array $errors = null;

    public function validate($config)
    {
        try {
            $config = $this->_validateEnvironment($config);
            $config = $this->_validateSenderID($config);
            $config = $this->_validateTest_accessToken($config);
            $config = $this->_validateProduction_accessToken($config);
            $config = $this->_validateBrandID($config);
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
        if (! isset($config->environment)) {
            throw new InvalidBluemConfigurationException(
                'environment not set; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        if (! in_array($config->environment, Constants::ENVIRONMENTS, true)) {
            throw new InvalidBluemConfigurationException(
                sprintf(
                    sprintf('Invalid environment setting (%s), should be one of: %%s', $config->environment),
                    implode(', ', Constants::ENVIRONMENTS)
                )
            );
        }

        return $config;
    }

    private function _validateSenderID($config)
    {
        if (! isset($config->senderID)) {
            throw new Exception(
                'senderID not set; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        if ($config->senderID === '') {
            throw new Exception(
                'senderID cannot be empty; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        if (! str_starts_with((string) $config->senderID, 'S')) {
            throw new Exception(
                'senderID always starts with an S followed by digits. Please correct this in your configuration when instantiating the Bluem integration'
            );
        }

        return $config;
    }

    private function _validateTest_accessToken($config)
    {
        if (
            $config->environment === Constants::TESTING_ENVIRONMENT
            && (! isset($config->test_accessToken) || $config->test_accessToken === '')
        ) {
            throw new Exception(
                'test_accessToken not set correctly; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        return $config;
    }

    private function _validateProduction_accessToken($config)
    {
        if (
            $config->environment === Constants::PRODUCTION_ENVIRONMENT
            && (! isset($config->production_accessToken) || $config->production_accessToken === '')
        ) {
            throw new Exception(
                'production_accessToken not set correctly; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        return $config;
    }

    private function _validateBrandID($config)
    {
        if (! isset($config->brandID)) {
            throw new RuntimeException(
                'brandID not set; please add this to your configuration when instantiating the Bluem integration'
            );
        }

        return $config;
    }

    private function _validateMerchantIDAndSelectAccessToken($config)
    {
        if (! isset($config->merchantId)) {
            $config->merchantId = '';
        }

        if ($config->environment === Constants::PRODUCTION_ENVIRONMENT) {
            $config->accessToken = $config->production_accessToken;
        } elseif ($config->environment === Constants::TESTING_ENVIRONMENT) {
            $config->accessToken = $config->test_accessToken;
            $config->merchantID = Constants::BLUEM_STATIC_MERCHANT_ID;
        }

        return $config;
    }

    private function _validateThanksPage($config)
    {
        return $config;
    }

    private function _validateExpectedReturnStatus($config): mixed
    {
        if ($config->environment === Constants::TESTING_ENVIRONMENT) {
            if (
                ! isset($config->expectedReturnStatus)
                || ($config->expectedReturnStatus !== '' && ! in_array($config->expectedReturnStatus, $this->getPossibleReturnStatuses(), true))
            ) {
                $config->expectedReturnStatus = Constants::EXPECTED_RETURN_SUCCESS;
            }
        } else {
            unset($config->expectedReturnStatus);
        }

        return $config;
    }

    private function getPossibleReturnStatuses(): array
    {
        return [
            Constants::EXPECTED_RETURN_NONE,
            Constants::EXPECTED_RETURN_SUCCESS,
            Constants::EXPECTED_RETURN_CANCELLED,
            Constants::EXPECTED_RETURN_EXPIRED,
            Constants::EXPECTED_RETURN_FAILURE,
            Constants::EXPECTED_RETURN_OPEN,
            Constants::EXPECTED_RETURN_PENDING,
        ];
    }

    private function _validateEMandateReason($config)
    {
        return $config;
    }

    private function _validateLocalInstrumentCode($config)
    {
        return $config;
    }

    private function _validateMerchantReturnURLBase($config)
    {
        return $config;
    }
}
