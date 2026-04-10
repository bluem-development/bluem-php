<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Requests\EmandateBluemRequest;

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
}
