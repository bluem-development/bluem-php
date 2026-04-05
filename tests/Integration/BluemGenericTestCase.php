<?php

/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Integration;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;
use Dotenv\Dotenv;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Abstract base class for all BluemPHP unit tests.
 */
abstract class BluemGenericTestCase extends TestCase
{
    /**
     * @var string[]
     */
    private const REQUIRED_ENVIRONMENT_VARIABLES = [
        'BLUEM_ENV',
        'BLUEM_SENDER_ID',
        'BLUEM_BRANDID',
        'BLUEM_TEST_ACCESS_TOKEN',
        'BLUEM_MERCHANTID',
        'BLUEM_MERCHANTRETURNURLBASE',
    ];

    /**
     * The Bluem integration object
     */
    protected Bluem $bluem;

    /**
     * Set up the required config and objects necessary for proper testing
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $env_file = __DIR__ . '/../..';
        $dotenv = Dotenv::createImmutable($env_file);
        $dotenv->safeLoad();

        foreach (self::REQUIRED_ENVIRONMENT_VARIABLES as $variable) {
            if (!isset($_ENV[$variable]) || $_ENV[$variable] === '') {
                $this->markTestSkipped(sprintf('Live Bluem integration tests require %s to be set.', $variable));
            }
        }

        // Create a Bluem object and set the Bluem configuration details based on your .env file.
        $bluem_config = new stdClass();
        $bluem_config->environment = $_ENV['BLUEM_ENV'];
        $bluem_config->senderID = $_ENV['BLUEM_SENDER_ID'];

        $bluem_config->brandID = $_ENV['BLUEM_BRANDID'];
        $bluem_config->test_accessToken = $_ENV['BLUEM_TEST_ACCESS_TOKEN'];
        $bluem_config->IDINBrandID = $_ENV['BLUEM_BRANDID'];
        $bluem_config->merchantID = $_ENV['BLUEM_MERCHANTID'];
        $bluem_config->merchantReturnURLBase = $_ENV['BLUEM_MERCHANTRETURNURLBASE'];

        $bluem_config->production_accessToken = "" ;
        $bluem_config->expectedReturnStatus = "success" ;
        $bluem_config->eMandateReason = "eMandateReason" ;
        $bluem_config->sequenceType = "OOFF" ;
        $bluem_config->localInstrumentCode = "B2B" ;
        // @todo: create env variables for these

        try {
            $this->bluem = new Bluem($bluem_config);
        } catch (\Exception $exception) {
            $this->fail("While initializing Bluem, " . $exception->getMessage() . " occurred");
        }
    }

    // test that we can set the configuration
    public function testSetConfiguration(): void
    {
        $setting = $this->bluem->setConfig("environment", true);

        // assert result is true
        $this->assertTrue($setting);
    }

    // test that we can get the configuration
    public function testGetConfiguration(): void
    {
        $this->bluem->setConfig("environment", "test");

        $result = $this->bluem->getConfig("environment");

        // assert result is string
        $this->assertIsString($result);
        $this->assertEquals("test", $result);
    }


    /**
     * Perform assertions based on a created BluemPHP Request object
     */
    protected function _finalizeBluemRequestAssertion(BluemRequestInterface $request): void
    {
        try {
            // $this->assertEquals($request->getStatus(), "success");
            $response = $this->bluem->PerformRequest($request);
        } catch (Exception $exception) {
            $this->fail(
                "Exception when performing the request: " .
                $exception->getMessage()
            );
        }

        if ($response instanceof \Bluem\BluemPHP\Responses\ErrorBluemResponse) {
            $this->fail(
                "Erroneous response returned: " .
                $response->error()
            );
        } else {
            $cname = $request::class;
            $this->assertTrue(true, sprintf('Can utilize %s request and perform it', $cname));
        }
    }
}
