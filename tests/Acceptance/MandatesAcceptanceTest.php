<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Helpers\BluemCurrency;
use Bluem\BluemPHP\Requests\EmandateBluemRequest;
use Bluem\BluemPHP\Responses\MandateStatusBluemResponse;
use Bluem\BluemPHP\Responses\MandateTransactionBluemResponse;

final class MandatesAcceptanceTest extends AcceptanceTestCase
{
    public function testMandateRequestXmlUsesSandboxSampleData(): void
    {
        $config = $this->createConfiguration('BluemMandate', [
            'localInstrumentCode' => 'CORE',
            'merchantReturnURLBase' => 'https://example.test/return',
        ]);

        $request = new EmandateBluemRequest(
            $config,
            '56789',
            '1234',
            '134426345',
            'success'
        );

        $request->addAdditionalData('CustomerName', 'INSERT_VARIABLE_CUSTOMER_NAME_HERE');
        $request->selectDebtorWallet('INGBNL2A');
        $request->setBrandId('BluemMandate');

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<EMandateTransactionRequest',
            'entranceCode="',
            '<MandateID>134426345</MandateID>',
            '<MerchantReturnURL automaticRedirect="1">https://example.test/return?mandateID=134426345</MerchantReturnURL>',
            '<SequenceType>RCUR</SequenceType>',
            '<EMandateReason>INSERT_EMANDATE_REASON_HERE</EMandateReason>',
            '<DebtorReference>56789</DebtorReference>',
            '<PurchaseID>56789-1234</PurchaseID>',
            '<CustomerName>INSERT_VARIABLE_CUSTOMER_NAME_HERE</CustomerName>',
            '<DebtorWallet>',
            '<INCASSOMACHTIGEN>',
            '<BIC>INGBNL2A</BIC>'
        );
    }

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
