<?php

namespace Bluem\BluemPHP\Responses;

/**
 * EMandateErrorResponse
 */
class ErrorBluemResponse {
    public function __construct(private string $error)
    {
    }

    public function Status(): bool {
        return false;
    }

    public function ReceivedResponse(): bool {
        return false;
    }

    public function Error(): string {
        return $this->error;
    }
}
