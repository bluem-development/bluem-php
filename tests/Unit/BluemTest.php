<?php

namespace Unit;

use Bluem\BluemPHP\Bluem;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

class BluemTest extends TestCase
{
    private Bluem $bluem;

    protected function setUp(): void
    {
        parent::setUp();
        $bluem_config = $this->getConfig();

        try {
            $this->bluem = new Bluem(
                $bluem_config
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCreateMandateRequest()
    {
        $this->markTestSkipped("Assertions need to be added");
        // arrange
        // act
        $request = $this->bluem->CreateMandateRequest("customer_id", "order_id");
        // assertions

    }

    /** @dataProvider mandateIdTestDataProvider */
    public function testCanCreateMandateID($orderId, $customerId, $expectedMandateId): void
    {
        $mandateId = $this->bluem->CreateMandateID($orderId, $customerId);
        $this->assertIsString($mandateId);
//        $this->assertEquals( $expectedMandateId , $mandateId, );
    }

    public function mandateIdTestDataProvider()
    {
        return [
            [
                'orderId'=>1,
                'customerId'=>1,
                'expectedMandateId'=>"",
            ]
        ];
    }

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
