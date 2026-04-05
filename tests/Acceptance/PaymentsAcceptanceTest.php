<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Responses\PaymentStatusBluemResponse;
use Bluem\BluemPHP\Responses\PaymentTransactionBluemResponse;

final class PaymentsAcceptanceTest extends AcceptanceTestCase
{
    public function testPaymentRequestXmlUsesSandboxSampleData(): void
    {
        $config = $this->createConfiguration('S001Payment', [
            'merchantReturnURLBase' => 'http://localhost:8000/?a=callback',
        ]);

        $request = new PaymentBluemRequest(
            $config,
            'Beschrijving',
            '1234',
            12.34,
            '2026-04-12',
            'EUR',
            'TRANS123',
            'PAYMENT-ENTRANCE-123'
        );

        $request->selectDebtorWallet('INGBNL2A');
        $request->setPaymentMethodToBancontact();

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<PaymentTransactionRequest',
            'entranceCode="PAYMENT-ENTRANCE-123"',
            '<Description>Beschrijving</Description>',
            '<DebtorReference>1234</DebtorReference>',
            '<Currency>EUR</Currency>',
            '<Amount>12.34</Amount>',
            '<DueDateTime>2026-04-12T00:00:00.000Z</DueDateTime>',
            '<DebtorReturnURL automaticRedirect="1">http://localhost:8000/?a=callback?entranceCode=PAYMENT-ENTRANCE-123&amp;transactionID=TRANS123</DebtorReturnURL>',
            '<DebtorWallet>',
            '<Bancontact>',
        );
    }

    public function testPaymentRequestSupportsCreditCardMethodBranch(): void
    {
        $config = $this->createConfiguration('S001Payment');

        $request = new PaymentBluemRequest(
            $config,
            'Beschrijving',
            '1234',
            12.34,
            '2026-04-12',
            'EUR',
            'TRANS456',
            'PAYMENT-ENTRANCE-456'
        );

        $request->setPaymentMethodToCreditCard(
            '1234000012340000',
            'John Doe',
            '123',
            '03',
            '2025'
        );

        self::assertTrue($request->getContext()->isCreditCard());

        $xml = $request->XmlString();
        $this->assertXmlContains(
            $xml,
            '<CreditCard>',
            '<CardNumber>1234000012340000</CardNumber>',
            '<Name>John Doe</Name>',
            '<SecurityCode>123</SecurityCode>',
            '<ExpirationDate>',
            '<Month>03</Month>',
            '<Year>2025</Year>'
        );
    }

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
