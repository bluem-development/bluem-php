<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;
use Bluem\BluemPHP\Responses\MandateTransactionBluemResponse;

final class MandateResponseTest extends ResponseTestCase
{
    public function testMandateTransactionResponseReadsTransactionData(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EMandateInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <EMandateTransactionResponse entranceCode="MANDATE-ENTRANCE-123">
        <TransactionURL>https://test.viamijnbank.net/mandate/transaction/134426345</TransactionURL>
        <TransactionID>MANDATE-TX-123</TransactionID>
        <MandateID>134426345</MandateID>
    </EMandateTransactionResponse>
</EMandateInterface>
XML;

        $response = $this->loadXmlResponse($xml, MandateTransactionBluemResponse::class);

        self::assertSame('https://test.viamijnbank.net/mandate/transaction/134426345', $response->GetTransactionURL());
        self::assertSame('MANDATE-TX-123', $response->GetTransactionID());
        self::assertSame('134426345', $response->GetMandateID());
    }

    public function testMandateStatusResponseReadsAcceptanceReportData(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EMandateInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <EMandateStatusUpdate entranceCode="MANDATE-STATUS-123">
        <EMandateStatus>
            <AcceptanceReport>
                <DebtorIBAN>NL66ABNA4097012428</DebtorIBAN>
                <DebtorBankID>ABNANL2A</DebtorBankID>
                <DebtorAccountName>D.J.M. Daan Jeroen Maarten Quackernaat</DebtorAccountName>
                <MaxAmount>250.00</MaxAmount>
            </AcceptanceReport>
        </EMandateStatus>
    </EMandateStatusUpdate>
</EMandateInterface>
XML;

        $response = $this->loadXmlResponse($xml, MandateStatusBluemResponse::class);

        self::assertSame('NL66ABNA4097012428', $response->GetDebtorIBAN());
        self::assertSame('ABNANL2A', $response->GetDebtorBankID());
        self::assertSame('D.J.M. Daan Jeroen Maarten Quackernaat', $response->GetDebtorAccountName());
        self::assertSame(250.00, $response->GetMaximumAmount()->amount);
        self::assertSame('EUR', $response->GetMaximumAmount()->currency->code);
    }
}
