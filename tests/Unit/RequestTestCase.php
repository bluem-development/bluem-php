<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Helpers\BluemConfiguration;
use PHPUnit\Framework\TestCase;

abstract class RequestTestCase extends TestCase
{
    protected function createConfiguration(array $overrides = []): BluemConfiguration
    {
        return new BluemConfiguration(array_merge(
            [
                'environment' => 'test',
                'senderID' => 'S001',
                'test_accessToken' => 'INSERT_TEST_ACCESS_TOKEN_HERE',
                'production_accessToken' => '',
                'brandID' => 'BluemTest',
                'merchantID' => 'INSERT_MERCHANT_ID_HERE',
                'merchantReturnURLBase' => 'https://example.test/return',
                'expectedReturnStatus' => 'success',
                'eMandateReason' => 'INSERT_EMANDATE_REASON_HERE',
                'localInstrumentCode' => 'CORE',
            ],
            $overrides
        ));
    }

    protected function assertXmlContains(string $xml, string ...$fragments): void
    {
        foreach ($fragments as $fragment) {
            self::assertStringContainsString($fragment, $xml);
        }
    }
}

