<?php

require_once __DIR__ . '\BluemGenericTest.php';

class MandateRequestTest extends BluemGenericTest
{
    public function testCanCreateMandateRequest()
    {
        $customer_id = "testcustomer001";
        $order_id = "testorder01231";
        
        $request = $this->bluem->CreateMandateRequest(
            $customer_id,
            $order_id,
            "default"
        );
        $this->assertInstanceOf(
            \Bluem\BluemPHP\Requests\EmandateBluemRequest::class, 
            $request
        );

        $this->_finalizeBluemRequestAssertion($request);
    }
}
