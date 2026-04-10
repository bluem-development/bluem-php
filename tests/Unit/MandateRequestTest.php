<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Requests\EmandateBluemRequest;
use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;

final class MandateRequestTest extends RequestTestCase
{
    public function testCreateMandateRequestMatchesExampleShape(): void
    {
        $bluem = $this->createBluem();

        $request = $bluem->CreateMandateRequest(
            '56789',
            '1234',
            'MANDATE-123'
        );

        self::assertInstanceOf(EmandateBluemRequest::class, $request);

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<EMandateTransactionRequest',
            'entranceCode="HIO100OIH',
            '<MandateID>MANDATE-123</MandateID>',
            '<MerchantReturnURL automaticRedirect="1">https://example.test/return?mandateID=MANDATE-123</MerchantReturnURL>',
            '<SequenceType>RCUR</SequenceType>',
            '<EMandateReason>INSERT_EMANDATE_REASON_HERE</EMandateReason>',
            '<DebtorReference>56789</DebtorReference>'
        );

        self::assertMatchesRegularExpression(
            '#entranceCode="HIO100OIH\\d{17}"#',
            $xml
        );

        self::assertStringContainsString('<PurchaseID>56789-1234</PurchaseID>', $xml);
    }

    public function testMandateStatusResponseMatchesTheExampleStatusFlow(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EMandateInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <EMandateStatusUpdate entranceCode="MANDATE-ENTRANCE-123">
        <EMandateStatus>
            <Status>Success</Status>
            <AcceptanceReport>
                <DebtorIBAN>NL66ABNA4097012428</DebtorIBAN>
                <DebtorBankID>ABNANL2A</DebtorBankID>
                <DebtorAccountName>D.J.M. Daan Jeroen Maarten Quackernaat</DebtorAccountName>
                <MaxAmount>250.00</MaxAmount>
            </AcceptanceReport>
        </EMandateStatus>
    </EMandateStatusUpdate>
</EMandateInterface>
XML
;

        $response = simplexml_load_string($xml, MandateStatusBluemResponse::class);

        self::assertInstanceOf(MandateStatusBluemResponse::class, $response);
        self::assertTrue($response->Status());
        self::assertSame('Success', $response->EMandateStatusUpdate->EMandateStatus->Status . '');
        self::assertSame('NL66ABNA4097012428', $response->GetDebtorIBAN());
        self::assertSame('ABNANL2A', $response->GetDebtorBankID());
        self::assertSame('D.J.M. Daan Jeroen Maarten Quackernaat', $response->GetDebtorAccountName());
        self::assertSame(250.00, $response->GetMaximumAmount()->amount);
        self::assertSame('EUR', $response->GetMaximumAmount()->currency->code);

        self::assertStringContainsString('<AcceptanceReport>', $xml);
    }

    public function testMandateStatusWithMissingAcceptanceReportThrowsWhenReadingMaximumAmount(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EMandateInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <EMandateStatusUpdate entranceCode="MANDATE-ENTRANCE-123">
        <EMandateStatus>
            <Status>Success</Status>
        </EMandateStatus>
    </EMandateStatusUpdate>
</EMandateInterface>
XML;

        $response = simplexml_load_string($xml, MandateStatusBluemResponse::class);

        self::assertInstanceOf(MandateStatusBluemResponse::class, $response);

        $this->expectExceptionMessage('No acceptance report delivered');

        $response->GetMaximumAmount();
    }

    public function testCreateMandateRequestRejectsEmptyIdentifiers(): void
    {
        $bluem = $this->createBluem();

        $this->expectExceptionMessage('Customer ID Not set');

        $bluem->CreateMandateRequest('', '1234', 'MANDATE-123');
    }

    public function testCreateMandateRequestRejectsEmptyOrderId(): void
    {
        $bluem = $this->createBluem();

        $this->expectExceptionMessage('Order ID Not set');

        $bluem->CreateMandateRequest('56789', '', 'MANDATE-123');
    }

    private function createBluem(array $overrides = []): Bluem
    {
        return new Bluem($this->createConfiguration($overrides));
    }
}

