<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Bluem\BluemPHP\Helpers;

use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use Bluem\BluemPHP\Validators\BluemConfigurationValidator;
use Exception;
use RuntimeException;

final class BluemConfiguration
{
    private const TESTING_ENVIRONMENT = 'test';

    public mixed $environment;
    public mixed $senderID;
    public mixed $brandID;
    public mixed $accessToken;
    public mixed $merchantReturnURLBase;
    private BluemConfigurationValidator $validator;
    public mixed $test_accessToken;
    public mixed $IDINBrandID;
    public mixed $sequenceType;
    public mixed $merchantID;
    public string $production_accessToken;
    public string $expectedReturnStatus;
    public string $eMandateReason;
    public string $localInstrumentCode;

    /**
     * this is given by the bank and never changed (default 0)
     */
    public string $merchantSubID;
    private string $PaymentsBrandID;
    // @todo: consider deprecating this?
    private string $EmandateBrandID;
    // @todo: consider deprecating this?

    // additional helper flags
    /**
     * Allows for testing webhook on local environments with no HTTPS check and verbose output.
     */
    public bool $webhookDebug = false;

    /**
     * An object containing the configuration for the Bluem integration. Can be an array or object
     *
     * @param object|array $raw
     *
     * @throws Exception
     */
    public function __construct(object|array $raw)
    {
        if (is_array($raw))
        {
            $raw = (object) $raw;
        }

        $this->validator = new BluemConfigurationValidator();

        $validated = $this->validator->validate($raw);

        if ($validated === false) {
            throw new InvalidBluemConfigurationException('Bluem Configuration is not valid: ' . $this->errorsAsString());
        }

        $this->environment           = $validated->environment ?? self::TESTING_ENVIRONMENT;
        $this->senderID              = $validated->senderID;
        $this->brandID               = $validated->brandID;
        $this->accessToken           = $validated->accessToken;

        $this->merchantReturnURLBase = $validated->merchantReturnURLBase ?? null;
        // @todo: if this is required, break. check that

        $this->test_accessToken = $validated->test_accessToken ?? null;

        $this->IDINBrandID = $this->_assumeBrandID("Identity", $this->brandID);
        $this->PaymentsBrandID = $this->_assumeBrandID("Payment", $this->brandID);
        $this->EmandateBrandID = $this->_assumeBrandID("Mandate", $this->brandID);

        $this->sequenceType = $validated->sequenceType ?? null;

        $this->merchantID             = $validated->merchantID ?? null;
        $this->production_accessToken = $validated->production_accessToken ?? null;
        $this->expectedReturnStatus   = $validated->expectedReturnStatus ?? null;
        $this->eMandateReason         = $validated->eMandateReason ?? null;
        $this->localInstrumentCode    = $validated->localInstrumentCode ?? null;
        $this->merchantSubID          = "0";

        $this->webhookDebug = false;
    }

    public function errorsAsString(): string {
        return implode( ", ", $this->validator->errors() );
    }

    /**
     * Assume a brandID for a service based on another valid brandID
     */
    private function _assumeBrandID(string $service, string $brandID): string
    {
        if (empty($brandID)) {
            throw new RuntimeException("No brandID given");
        }

        $available_services = [
            'Identity',
            'Payment',
            'Mandate'
        ];

        if (!in_array($service, $available_services)) {
            throw new RuntimeException("Invalid service requested");
        }

        $prefix = str_replace($available_services, '', $brandID);
        return $prefix.ucfirst($service);
    }

    /**
     * Override the brandID for a specific service
     */
    public function setBrandId(string $selectedBrandID): void
    {
        $this->brandID = $selectedBrandID;
    }

    public function errors(): array {
        return $this->validator->errors();
    }
}
