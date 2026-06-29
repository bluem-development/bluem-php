<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Transport;

final class HttpTransportResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
    ) {
    }
}
