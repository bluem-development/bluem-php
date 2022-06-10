<?php

namespace Bluem\BluemPHP\Helpers;

use Bluem\BluemPHP\Models\Currency;


class MaxAmount {

    /**
     * @var Currency
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
        Currency $currency
    ) {
        // @todo: validate the amount to be nonnegative and have a maximum, see xsd
        $this->amount = $amount;

        $this->currency = $currency;
    }

    public function __toString(): string {
        return strval($this->amount);
    }

}