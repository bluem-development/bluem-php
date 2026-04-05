<?php

declare(strict_types=1);

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

    public function __construct(
        public float $amount,
        string $currencyCode
    ) {
        try {

            $this->currency = new BluemCurrency($currencyCode);
        } catch (\Exception) {
            $this->currency = new BluemCurrency();
        }
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }
}
