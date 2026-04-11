<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Exceptions\InvalidBluemRequestException;
use Bluem\BluemPHP\Requests\PaymentBluemRequest;
use Bluem\BluemPHP\Responses\PaymentStatusBluemResponse;

final class PaymentRequestTest extends RequestTestCase
{
    public function testCreatePaymentRequestMatchesExampleShape(): void
    {
        $bluem = $this->createBluem();

        $request = $bluem->CreatePaymentRequest(
            'Test payment',
            '1234023',
            100.00,
            '2026-04-12',
            'EUR',
            'PAYMENT-ENTRANCE-123',
            ''
        );

        self::assertInstanceOf(PaymentBluemRequest::class, $request);

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<PaymentTransactionRequest',
            'entranceCode="PAYMENT-ENTRANCE-123"',
            '<Description>Test payment</Description>',
            '<DebtorReference>1234023</DebtorReference>',
            '<Currency>EUR</Currency>',
            '<Amount>100.00</Amount>',
            '<DueDateTime>2026-04-12T00:00:00.000Z</DueDateTime>'
        );

        self::assertMatchesRegularExpression(
            '#<DebtorReturnURL automaticRedirect="1">https://example\.test/return\?entranceCode=PAYMENT-ENTRANCE-123&amp;transactionID=1234023\\d{8}</DebtorReturnURL>#',
            $xml
        );
        self::assertMatchesRegularExpression(
            '#<PaymentReference>12340231234023\\d{8}</PaymentReference>#',
            $xml
        );
    }

    public function testPaymentStatusResponseMatchesTheExampleStatusFlow(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EPaymentInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <PaymentStatusUpdate entranceCode="PAYMENT-ENTRANCE-123">
        <CreationDateTime>2026-04-05T00:00:00Z</CreationDateTime>
        <PaymentReference>1234134426345ae</PaymentReference>
        <DebtorReference>1234</DebtorReference>
        <TransactionID>TRANS123</TransactionID>
        <Status>Success</Status>
        <Amount>12.34</Amount>
        <AmountPaid>12.34</AmountPaid>
        <Currency>EUR</Currency>
        <PaymentMethod>IDEAL</PaymentMethod>
    </PaymentStatusUpdate>
</EPaymentInterface>
XML;

        $response = simplexml_load_string($xml, PaymentStatusBluemResponse::class);

        self::assertInstanceOf(PaymentStatusBluemResponse::class, $response);
        self::assertTrue($response->Status());
        self::assertSame('Success', $response->GetStatusCode());
        self::assertSame('TRANS123', $response->PaymentStatusUpdate->TransactionID . '');
        self::assertSame('1234', $response->PaymentStatusUpdate->DebtorReference . '');

        self::assertStringContainsString('<TransactionID>TRANS123</TransactionID>', $xml);
    }

    public function testCreatePaymentRequestRejectsUnsupportedCurrency(): void
    {
        $bluem = $this->createBluem();

        $this->expectException(InvalidBluemRequestException::class);

        $bluem->CreatePaymentRequest(
            'Test payment',
            '1234023',
            100.00,
            '2026-04-12',
            'USD',
            'PAYMENT-ENTRANCE-123'
        );
    }

    private function createBluem(array $overrides = []): Bluem
    {
        return new Bluem($this->createConfiguration($overrides));
    }
}

