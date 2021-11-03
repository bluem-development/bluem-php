<?php

class CanCreatePaymentEntranceCodeTest extends BluemGenericTest
{
    public function testCanCreatePaymentEntranceCode()
    {
        $entranceCode = $this->bluem->CreateEntranceCode();
        if (is_string($entranceCode) && $entranceCode!=="") {
            $this->assertTrue(true, "Valid entranceCode generated");
        } else {
            $this->assertTrue(false, "Invalid entranceCode generated");
        }
    }
}
