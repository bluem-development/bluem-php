<?php

namespace Bluem\BluemPHP\Helpers;

class BluemMaxAmount {

    /**
     * @var BluemCurrency
     */
    public $currency;

    /**
     * @var float
     */
    public $amount;

    /**
     * @param float $amount
     * @param string $currency
     */
    function __construct(
        float $amount,
        string $currency
    ) {
        // @todo: validate the amount to be nonnegative and have a maximum, see xsd
        $this->amount = $amount;

        $this->currency = new BluemCurrency( $currency );
    }

    public function __toString(): string {
        return $this->amount;
    }

}