<?php

require_once __DIR__ . '\BluemGenericTest.php';

class CanCreatePaymentRequestTest extends BluemGenericTest
{
    public function testCanCreateRequest()
    {
        $description = "Test payment";
        $amount = 100.00;
        $currency = "EUR";
        $debtorReference = "1234023";
        $dueDateTime = null;
        $returnUrl = "";
        $entranceCode = $this->bluem->CreateEntranceCode();

        // To create AND perform a request:
        $request = $this->bluem->CreatePaymentRequest(
            $description,
            $debtorReference,
            $amount,
            $dueDateTime,
            $currency,
            $entranceCode,
            $returnUrl
        );
        $this->assertTrue($request instanceof \Bluem\BluemPHP\Requests\PaymentBluemRequest);

        $this->_finalizeBluemRequestAssertion($request);
    }
}
