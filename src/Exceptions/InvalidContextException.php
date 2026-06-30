<?php

declare(strict_types=1);

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Exceptions;

use Bluem\BluemPHP\Constants;
use Exception;

class InvalidContextException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = "Invalid Context requested, should be
                one of the following: " .
            implode(",", Constants::AVAILABLE_CONTEXTS);
    }
}
