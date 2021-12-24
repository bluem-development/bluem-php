<?php

namespace Bluem\Tests\Unit;
require_once __DIR__ . '\BluemGenericTest.php';

class BluemVerifyIPTest extends BluemGenericTest
{
    /**
     * @covers Bluem::VerifyIPIsNetherlands
     * @return void
     */
    public function testVerifyIPIsNetherlands()
    {
        
        $result = $this->bluem->VerifyIPIsNetherlands();
        $this->assertIsBool($result);
        $this->assertEquals(false,$result);
    }
}
