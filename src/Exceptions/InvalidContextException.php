<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Exceptions;

use Exception;

class InvalidContextException extends Exception
{
    public const AVAILABLE_CONTEXTS = [ "Mandates", "Payments", "Identity" ];

    public function __construct()
    {
        parent::__construct();

        $this->message = "Invalid Context requested, should be
                one of the following: " .
            implode(",", self::AVAILABLE_CONTEXTS);
    }
}
