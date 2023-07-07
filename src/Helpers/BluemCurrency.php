<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Bluem\BluemPHP\Helpers;

use RuntimeException;
use Stringable;

class BluemCurrency implements Stringable
{

    private const EURO_CURRENCY = 'EUR';
    private const US_DOLLAR_CURRENCY = 'USD';

    public string $code;
    /**
     * @var string[]
     */
    private array $allowed_currencies = [ self::EURO_CURRENCY, self::US_DOLLAR_CURRENCY ];

    public function __construct( string $code = self::EURO_CURRENCY )
    {

        if (!in_array($code, $this->allowed_currencies, true)) {
            throw new RuntimeException("Currency code $code not allowed");
        }

        $this->code = $code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
