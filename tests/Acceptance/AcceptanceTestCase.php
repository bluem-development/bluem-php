<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Helpers\BluemConfiguration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

/**
 * Shared helpers for acceptance-style request/response tests.
 *
 * These suites intentionally stay offline: they assert request XML and parse
 * representative response fixtures, so they can be copied into the Bluem PHP
 * repository and run without live bank credentials.
 */
abstract class AcceptanceTestCase extends TestCase
{
    /**
     * Build a minimal but valid Bluem configuration for test environment usage.
     *
     * Unknown live values are kept as placeholders so the file stays copy-pastable.
     */
    protected function createConfiguration(string $brandId, array $overrides = []): BluemConfiguration
    {
        $config = array_merge(
            [
                'environment' => 'test',
                'senderID' => 'S001',
                'test_accessToken' => 'INSERT_TEST_ACCESS_TOKEN_HERE',
                'production_accessToken' => '',
                'brandID' => $brandId,
                'merchantID' => 'INSERT_MERCHANT_ID_HERE',
                'merchantReturnURLBase' => 'https://example.test/return',
                'expectedReturnStatus' => 'success',
                'eMandateReason' => 'INSERT_EMANDATE_REASON_HERE',
                'localInstrumentCode' => 'CORE',
            ],
            $overrides
        );

        return new BluemConfiguration((object) $config);
    }

    /**
     * Load a SimpleXML fixture as one of Bluem's response subclasses.
     */
    protected function loadXmlResponse(string $xml, string $className): SimpleXMLElement
    {
        $response = simplexml_load_string($xml, $className);

        if (!$response instanceof SimpleXMLElement) {
            throw new RuntimeException('Unable to parse XML fixture for ' . $className);
        }

        return $response;
    }

    /**
     * Assert that a string contains all expected fragments.
     */
    protected function assertXmlContains(string $xml, string ...$fragments): void
    {
        foreach ($fragments as $fragment) {
            self::assertStringContainsString($fragment, $xml);
        }
    }
}
