<?php

namespace Bluem\BluemPHP\Helpers;

class BluemMaxAmount implements \Stringable {

    /**
     * @var BluemCurrency
     */
    public $currency;

    /**
     * @var float
     */
    public $amount;

    function __construct(
        float $amount,
        string $currency
    ) {
        // @todo: validate the amount to be nonnegative and have a maximum, see xsd
        $this->amount = $amount;

        $this->currency = new BluemCurrency( $currency );
    }

    public function __toString(): string {
        return (string) $this->amount;
    }

}