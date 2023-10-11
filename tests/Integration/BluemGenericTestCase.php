<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Integration;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Dotenv\Dotenv;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;

/**
 * Abstract base class for all BluemPHP unit tests.
 */
abstract class BluemGenericTestCase extends TestCase
{
    /**
     * The Bluem integration object
     */
    protected Bluem $bluem;

    /**
     * Set up the required config and objects necessary for proper testing
     *
     * @return void
     * @throws \Exception
     */
    protected function setUp() : void
    {
        $env_file =__DIR__. '/../..';
        $dotenv = Dotenv::createImmutable($env_file);
        $dotenv->load();

        // Create a Bluem object and set the Bluem configuration details based on your .env file.
        $bluem_config = new stdClass;
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
        } catch (\Exception $e) {
            $this->fail("While initializing Bluem, ".$e->getMessage()." occurred");
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
     *
     * @param BluemRequestInterface $request
     * @return void
     */
    protected function _finalizeBluemRequestAssertion(BluemRequestInterface $request) :void
    {
        try {
            // $this->assertEquals($request->getStatus(), "success");
            $response = $this->bluem->PerformRequest($request);
        } catch (Exception $e) {
            $this->fail(
                "Exception when performing the request: " .
                $e->getMessage()
            );
        }

        if (is_a($response, ErrorBluemResponse::class, false)) {
            $this->fail(
                "Erroneous response returned: " .
                $response->error()
            );
        } else {
            $cname = get_class($request);
            $this->assertTrue(true, "Can utilize {$cname} request and perform it");
        }
    }
}
