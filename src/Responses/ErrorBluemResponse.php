<?php

namespace Bluem\BluemPHP\Responses;

/**
 * EMandateErrorResponse
 */
class ErrorBluemResponse
{
    private $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }

    public function Status(): bool
    {
        return false;
    }

    public function ReceivedResponse()
    {
        return false;
    }

    public function Error()
    {
        return $this->error;
    }
}
