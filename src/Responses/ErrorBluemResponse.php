<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

use Bluem\BluemPHP\Interfaces\BluemResponseInterface;

/**
 * EMandateErrorResponse
 */
class ErrorBluemResponse implements BluemResponseInterface {
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
