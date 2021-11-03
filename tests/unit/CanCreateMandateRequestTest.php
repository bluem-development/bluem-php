<?php

require_once __DIR__ . '\BluemGenericTest.php';

class CanCreateMandateRequestTest extends BluemGenericTest
{
    public function testCanCreateRequest()
    {
        $customer_id = "testcustomer001";
        $order_id = "testorder01231";
        
        $request = $this->bluem->CreateMandateRequest(
            $customer_id,
            $order_id,
            "default"
        );
        $this->assertTrue($request instanceof \Bluem\BluemPHP\Requests\EmandateBluemRequest);

        $this->_finalizeBluemRequestAssertion($request);
    }
}
