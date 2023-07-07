<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Integration;

use Bluem\BluemPHP\Extensions\IPAPI;
use PHPUnit\Framework\TestCase;

class IPAPITest extends TestCase
{
    protected function setUp(): void
    {
        $this->IPAPI = new IPAPI();
    }

    public function testCheckIsNetherlandsReturnsTrueIfNoIPAddressGiven(): void
    {
        $result = $this->IPAPI->checkIsNetherlands();
        $this->assertTrue($result);
    }

    /** @dataProvider NetherlandsIPTestDataProvider */
    public function testCheckIPAdressGivenDataProvider($ipAddress, $expectedNetherlands): void
    {
        $isNetherlands = $this->IPAPI->checkIsNetherlands($ipAddress);

        $this->assertEquals($expectedNetherlands, $isNetherlands);
    }

    public function NetherlandsIPTestDataProvider(): array
    {
        return [
            [
                'ipAddress'=>'31.187.128.0',
                '$expectedNetherlands' => true,
            ]
            // @todo: add true negative test and check for usage rate limits to prevent false negatives.
        ];
    }

    public function testQueryIP(): void
    {
        $result = $this->IPAPI->QueryIP();

        $this->assertEquals(null, $result);
    }

    public function testGetCurrentIP(): void
    {
        $result = $this->IPAPI->checkIsNetherlands();
        $this->assertTrue($result);
    }
}
