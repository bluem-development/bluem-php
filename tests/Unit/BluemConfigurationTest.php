<?php

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use PHPUnit\Framework\TestCase;
use stdClass;

if (!defined("BLUEM_ENVIRONMENT_PRODUCTION")) {
    define("BLUEM_ENVIRONMENT_PRODUCTION", "prod");
}
if (!defined("BLUEM_ENVIRONMENT_TESTING")) {
    define("BLUEM_ENVIRONMENT_TESTING", "test");
}
if (!defined("BLUEM_ENVIRONMENT_ACCEPTANCE")) {
    define("BLUEM_ENVIRONMENT_ACCEPTANCE", "acc");
}
if (!defined("BLUEM_STATIC_MERCHANT_ID")) {
    define("BLUEM_STATIC_MERCHANT_ID", "0020000387");
}

class BluemConfigurationTest extends TestCase
{
    public function testConstructorWithValidData(): void
    {
        $configData = $this->getValidConfig();

        $bluemConfig = new BluemConfiguration($configData);

        $this->assertInstanceOf(BluemConfiguration::class, $bluemConfig);
        $this->assertEquals('test', $bluemConfig->environment);
        // Add other assertions for different properties...
    }

    public function testConstructorWithInvalidData(): void
    {
        $this->expectException(InvalidBluemConfigurationException::class);
        $invalidConfigData = new stdclass();

        new BluemConfiguration($invalidConfigData);
    }

    public function testSetBrandId(): void
    {
        $configData = $this->getValidConfig();

        $bluemConfig = new BluemConfiguration($configData);
        $bluemConfig->setBrandId('NewBrand');

        $this->assertEquals('NewBrand', $bluemConfig->brandID);
    }

    private function getValidConfig(): stdClass
    {
        $bluem_config = new stdClass();
        $bluem_config->environment = 'test';
        $bluem_config->senderID = 'S12345';

        $bluem_config->brandID = 'BLUEM_BRANDID';
        $bluem_config->test_accessToken = 'BLUEM_TEST_ACCESS_TOKEN';
        $bluem_config->IDINBrandID = 'BLUEM_BRANDID';
        $bluem_config->merchantID = 'BLUEM_MERCHANTID';
        $bluem_config->merchantReturnURLBase = 'BLUEM_MERCHANTRETURNURLBASE';

        $bluem_config->production_accessToken = "";
        $bluem_config->expectedReturnStatus = "success";
        $bluem_config->eMandateReason = "eMandateReason";
        $bluem_config->sequenceType = "OOFF";
        $bluem_config->localInstrumentCode = "B2B";
        return $bluem_config;
    }
}

