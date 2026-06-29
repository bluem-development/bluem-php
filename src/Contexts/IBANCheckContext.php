<?php

declare(strict_types=1);

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Contexts;

class IBANCheckContext extends BluemContext
{
    #[\Override]
    public function getValidationSchema(): string
    {
        return parent::getValidationSchema() . 'IBANCheck.xsd';
    }
}
