<?php

namespace Integration;

use Bluem\BluemPHP\Extensions\IPAPI;
use PHPUnit\Framework\TestCase;

class IPAPITest extends TestCase
{
    protected function setUp(): void
    {
        $this->IPAPI = new IPAPI();
    }

    public function testCheckIsNetherlandsReturnsTrueIfNoIPAddressGiven()
    {
        $result = $this->IPAPI->checkIsNetherlands();
        $this->assertTrue($result);
    }
    /** @dataProvider NetherlandsIPTestDataProvider */
    public function testCheckIPAdressGivenDataProvider($ipAddress,$expectedNetherlands): void
    {
        $isNetherlands = $this->IPAPI->checkIsNetherlands($ipAddress);
        $this->assertEquals($expectedNetherlands, $isNetherlands);
    }

    public function NetherlandsIPTestDataProvider(): array
    {
        return [
            [
                'ipAddress'=>'123.1.1.1',
                '$expectedNetherlands' => true,
            ],
//            [
//                'ipAddress'=>'100.52.65.9',
//                '$expectedNetherlands' => false,
//            ]
            // @todo: add more test cases
        ];
    }

    public function testQueryIP()
    {
        $result = $this->IPAPI->QueryIP();

        $this->assertEquals(null, $result);
    }

    public function testGetCurrentIP()
    {
        $result = $this->IPAPI->checkIsNetherlands("");
        $this->assertTrue($result);
    }
}
