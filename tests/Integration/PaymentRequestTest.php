<?php
namespace Bluem\BluemPHP\Tests\Integration;


class PaymentRequestTest extends BluemGenericTestCase
{
    public function testCanCreatePaymentRequest()
    {
        $description = "Test payment";
        $amount = 1.00;
        $currency = "EUR";
        $debtorReference = "1234567";
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

    public function testCanCreatePaymentEntranceCode()
    {
        $entranceCode = $this->bluem->CreateEntranceCode();
        $this->assertTrue(
            (is_string($entranceCode) && $entranceCode!==""),
            "Valid entranceCode generated"
        );
    }
}
