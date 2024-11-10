<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Interfaces;

interface BluemContextInterface
{
    public function getBICCodes(): array;
    public function getBICs(): array;
    public function getDebtorWalletElementName(): string;
    public function getValidationSchema(): string;
}
