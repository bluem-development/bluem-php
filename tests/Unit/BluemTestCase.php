<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use PHPUnit\Framework\TestCase;
use stdClass;

class BluemTestCase extends TestCase
{
    protected Bluem $bluem;

    /**
     * @throws InvalidBluemConfigurationException
     */
    protected function setUp(): void
    {
        // Mock the configuration as needed
        $mockedConfig = $this->getConfig();
        $this->bluem = new Bluem($mockedConfig);
    }


    // helper classes
    protected function getConfig(): stdClass
    {
        $bluem_config = new stdClass;
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
