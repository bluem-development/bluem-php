<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;
use Bluem\BluemPHP\Interfaces\BluemContextInterface;

abstract class BluemContext implements BluemContextInterface
{
    /** @var BIC[] $BICs */
    public function __construct(public array $BICs = [])
    {
    }

    /** @return BIC[] $BICs */
    public function getBICs(): array
    {
        return $this->BICs;
    }

    /** @return string[] $codes */
    public function getBICCodes(): array
    {
        $codes = [];
        foreach ($this->BICs as $BIC) {
            $codes[] = $BIC->issuerID;
        }

        return $codes;
    }

    public function getValidationSchema(): string
    {
        return __DIR__ . '/../../validation/';
    }

    public function getDebtorWalletElementName(): string
    {
        return '';
    }
}
