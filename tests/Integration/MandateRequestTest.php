<?php

namespace Integration;

use Bluem\BluemPHP\Requests\EmandateBluemRequest;

require_once __DIR__ . '/BluemGenericTest.php';

class MandateRequestTest extends BluemGenericTest
{
    public function testCanCreateMandateRequest()
    {
        $customer_id = "testcustomer001";
        $order_id = "testorder01231";

        try {
            $request = $this->bluem->CreateMandateRequest(
                $customer_id,
                $order_id,
                "default"
            );
        } catch (\Exception $e) {
            $this->fail("Exception while creating mandate request: ". $e->getMessage());
        }

        $this->assertInstanceOf(
            EmandateBluemRequest::class,
            $request
        );

        $this->_finalizeBluemRequestAssertion($request);
    }
}
