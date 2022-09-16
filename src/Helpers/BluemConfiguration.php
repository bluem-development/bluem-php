<?php

namespace Bluem\BluemPHP\Helpers;

use Bluem\BluemPHP\Validators\BluemConfigurationValidator;
use Exception;

class BluemConfiguration
{
    /**
     * @var mixed
     */
    public $environment;

    /**
     * @var mixed
     */
    public $senderID;

    /**
     * @var mixed
     */
    public $brandID;

    /**
     * @var mixed
     */
    public $accessToken;

    /**
     * @var mixed
     */
    public $merchantReturnURLBase;

    /**
     * @var BluemConfigurationValidator
     */
    private $validator;

    /**
     * @var mixed
     */
    public $test_accessToken;

    /**
     * @var mixed
     */
    public $IDINBrandID;

    /**
     * @var mixed
     */
    public $sequenceType;

    /**
     * @var mixed
     */
    public $merchantID;

    /**
     * @var string
     */
    public $production_accessToken;

    /**
     * @var string
     */
    public $expectedReturnStatus;

    /**
     * @var string
     */
    public $eMandateReason;

    /**
     * @var string
     */
    public $localInstrumentCode;

    /**
     * this is given by the bank and never changed (default 0)
     * @var string
     */
    public $merchantSubID;

    /**
     * @var string
     */
    private $PaymentsBrandID;
    // @todo: consider deprecating this?

    /**
     * @var string
     */
    private $EmandateBrandID;
    // @todo: consider deprecating this?

    // additional helper flags
    /**
     * Allows for testing webhook on local environments with no HTTPS check and verbose output.
     * @var bool
     */
    public bool $webhookDebug = false;

    /**
     * An object containing the configuration for the Bluem integration. Can be an array or object
     *
     * @param array|object $raw
     *
     * @throws Exception
     */
    public function __construct($raw)
    {
        if (is_array($raw))
        {
            $raw = (object) $raw;
        }

        $this->validator = new BluemConfigurationValidator();

        $raw_validated = $this->validator->validate($raw);

        if ($raw_validated === false) {
            throw new Exception('Bluem Configuration is not valid: ' . $this->errorsAsString());
        }

        $this->environment           = $raw_validated->environment;
        $this->senderID              = $raw_validated->senderID;
        $this->brandID               = $raw_validated->brandID;
        $this->accessToken           = $raw_validated->accessToken;

        $this->merchantReturnURLBase = $raw_validated->merchantReturnURLBase ?? null;
        // @todo: if this is required, break. check that

        $this->test_accessToken = $raw_validated->test_accessToken ?? null;

        $this->IDINBrandID = $this->_assumeBrandID("Identity", $this->brandID);
        $this->PaymentsBrandID = $this->_assumeBrandID("Payment", $this->brandID);
        $this->EmandateBrandID = $this->_assumeBrandID("Mandate", $this->brandID);

        $this->sequenceType = $raw_validated->sequenceType ?? null;

        $this->merchantID             = $raw_validated->merchantID;
        $this->production_accessToken = $raw_validated->production_accessToken ?? null;
        $this->expectedReturnStatus   = $raw_validated->expectedReturnStatus ?? null;
        $this->eMandateReason         = $raw_validated->eMandateReason ?? null;
        $this->localInstrumentCode    = $raw_validated->localInstrumentCode;
        $this->merchantSubID          = "0";

        $this->webhookDebug = false;
    }

    /**
     * @return string
     */
    public function errorsAsString(): string {
        return implode( ", ", $this->validator->errors() );
    }

    /**
     * Assume a brandID for a service based on another valid brandID
     *
     * @param string $prefix
     * @param string $brandID
     *
     * @return string
     * @throws Exception
     */
    private function _assumeBrandID(string $service, string $brandID) {
        if (empty($brandID)) {
            throw new Exception("No brandID given");
        }

        $available_services = [
            'Identity',
            'Payment',
            'Mandate'
        ];

        if (!in_array($service, $available_services)) {
            throw new Exception("Invalid service requested");
        }

        $prefix = str_replace($available_services, '', $brandID);
        return $prefix.ucfirst($service);
    }

    /**
     * Override the brandID for a specific service
     *
     * @param $selectedBrandID
     *
     * @return void
     */
    public function setBrandId( $selectedBrandID ) {
        $this->brandID = $selectedBrandID;
    }

    /**
     * @return array
     */
    public function errors(): array {
        return $this->validator->errors();
    }
}
