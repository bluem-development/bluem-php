<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Transport;

interface HttpTransportInterface
{
    public function send(string $url, array $headers, string $body): HttpTransportResponse;
}
