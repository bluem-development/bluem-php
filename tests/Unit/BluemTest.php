<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Contexts\IdentityContext;
use Bluem\BluemPHP\Exceptions\InvalidBluemConfigurationException;
use Bluem\BluemPHP\Interfaces\BluemResponseInterface;
use Bluem\BluemPHP\Requests\BluemRequest;
use Bluem\BluemPHP\Responses\ErrorBluemResponse;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class BluemTest extends TestCase
{
    private Bluem $bluem;

    /**
     * @throws InvalidBluemConfigurationException
     */
    protected function setUp(): void
    {
        // Mock the configuration as needed
        $mockedConfig = $this->getConfig();
        $this->bluem = new Bluem($mockedConfig);
    }



    public function testConstructorWithValidConfig(): void
    {
        $this->assertInstanceOf(Bluem::class, $this->bluem);
    }

    public function testConstructorWithInvalidConfig(): void
    {
        $this->expectException(InvalidBluemConfigurationException::class);
        new Bluem(null);
    }


    public function testMandateWithValidParameters(): void
    {
        // Mock the expected response
        $mockedResponse = $this->createMock(BluemResponseInterface::class);

        // Test the Mandate method with valid parameters
        $response = $this->bluem->Mandate('customer_id', 'order_id', 'mandate_id');

        // Assertions
        $this->assertInstanceOf(BluemResponseInterface::class, $response);
    }

    public function testMandateWithException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->bluem->Mandate('', '', '');
    }
    public function testCreateMandateID(): void
    {
        $mandateID = $this->bluem->CreateMandateID('order_id', 'customer_id');
        $this->assertIsString($mandateID);
    }
    public function testPerformRequestWithInvalidXml(): void
    {
        // Mock a request that would generate invalid XML
        $mockBluemRequest = $this->createMock(BluemRequest::class);

        $mockBluemRequest->method('XmlString')
            ->willReturn('<xmla>Some invalid aaXML String</xmla>');

        $mockBluemRequest->method('HttpRequestURL')
            ->willReturn('https://example.com/api/request');
        $mockBluemRequest->method('RequestContext')->willReturn(new IdentityContext());

        $result = $this->bluem->PerformRequest($mockBluemRequest);
        $this->assertInstanceOf(ErrorBluemResponse::class, $result);
    }

    // helper classes
    private function getConfig(): stdClass
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
