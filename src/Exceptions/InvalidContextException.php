<?php

namespace Bluem\BluemPHP\Exceptions;

use Exception;

class InvalidContextException extends Exception
{
    public const AVAILABLE_CONTEXTS = [ "Mandates", "Payments", "Identity" ];

    /**
     * @param string $string
     */
    public function __construct()
    {
        parent::__construct();

        $this->message = "Invalid Context requested, should be
                one of the following: " .
            implode( ",", self::AVAILABLE_CONTEXTS );
    }
}
