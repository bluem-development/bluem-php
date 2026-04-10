<?php

/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests;

use Bluem\BluemPHP\Transport\HttpTransportInterface;
use Bluem\BluemPHP\Transport\HttpTransportResponse;

final class FakeHttpTransport implements HttpTransportInterface
{
    public int $lastStatusCode = 0;

    public string $lastBody = '';

    /** @var string[] */
    public array $lastHeaders = [];

    public string $lastUrl = '';

    private int $nextStatusCode = 200;

    private string $nextBody = '';

    public function setResponse(int $statusCode, string $body): void
    {
        $this->nextStatusCode = $statusCode;
        $this->nextBody = $body;
    }

    public function send(string $url, array $headers, string $body): HttpTransportResponse
    {
        $this->lastUrl = $url;
        $this->lastHeaders = $headers;
        $this->lastBody = $body;
        $this->lastStatusCode = $this->nextStatusCode;

        return new HttpTransportResponse($this->nextStatusCode, $this->nextBody);
    }
}
