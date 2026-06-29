<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Responses\PaymentStatusBluemResponse;
use Bluem\BluemPHP\Responses\PaymentTransactionBluemResponse;

final class PaymentResponseTest extends ResponseTestCase
{
    public function testPaymentStatusResponseReadsStatusCode(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EPaymentInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <PaymentStatusUpdate entranceCode="1235414134">
        <CreationDateTime>2026-04-05T00:00:00Z</CreationDateTime>
        <PaymentReference>1234134426345ae</PaymentReference>
        <DebtorReference>1234</DebtorReference>
        <TransactionID>134426345ae</TransactionID>
        <Status>Success</Status>
        <Amount>12.34</Amount>
        <AmountPaid>12.34</AmountPaid>
        <Currency>EUR</Currency>
        <PaymentMethod>IDEAL</PaymentMethod>
    </PaymentStatusUpdate>
</EPaymentInterface>
XML;

        $response = $this->loadXmlResponse($xml, PaymentStatusBluemResponse::class);

        self::assertTrue($response->Status());
        self::assertSame('Success', $response->GetStatusCode());
    }

    public function testPaymentTransactionResponseReadsTransactionData(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EPaymentInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <PaymentTransactionResponse entranceCode="PAYMENT-ENTRANCE-123">
        <TransactionURL>https://test.viamijnbank.net/payment/transaction/TRANS123</TransactionURL>
        <TransactionID>TRANS123</TransactionID>
        <DebtorReference>1234</DebtorReference>
    </PaymentTransactionResponse>
</EPaymentInterface>
XML;

        $response = $this->loadXmlResponse($xml, PaymentTransactionBluemResponse::class);

        self::assertSame('https://test.viamijnbank.net/payment/transaction/TRANS123', $response->GetTransactionURL());
        self::assertSame('TRANS123', $response->GetTransactionID());
        self::assertSame('1234', $response->GetDebtorReference());
    }
}
