<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Helpers;

class BluemMaxAmount implements \Stringable
{
    public BluemCurrency $currency;
    public float $amount;

    public function __construct(
        float $amount,
        string $currencyCode
    ) {
        // @todo: validate the amount to be non-negative and have a maximum, see xsd
        $this->amount = $amount;

        try {
            $this->currency = new BluemCurrency($currencyCode);
        } catch (\Exception $e) {
            $this->currency = new BluemCurrency();
        }
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }
}
