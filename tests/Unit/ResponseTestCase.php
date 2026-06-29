<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

abstract class ResponseTestCase extends TestCase
{
    protected function loadXmlResponse(string $xml, string $className): SimpleXMLElement
    {
        $response = simplexml_load_string($xml, $className);

        if (!$response instanceof SimpleXMLElement) {
            throw new RuntimeException('Unable to parse XML fixture for ' . $className);
        }

        return $response;
    }
}
