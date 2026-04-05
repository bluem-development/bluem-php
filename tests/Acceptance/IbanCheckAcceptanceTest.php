<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Requests\IBANBluemRequest;

final class IbanCheckAcceptanceTest extends AcceptanceTestCase
{
    public function testIbanRequestXmlSanitizesInput(): void
    {
        $config = $this->createConfiguration('S001Payment');

        $request = new IBANBluemRequest(
            $config,
            'IBAN-ENTRANCE-123',
            'NL66 ABNA 4097 0124 28',
            'D.J.M.   Daan Jeroen Maarten Quackernaat   ',
            '1234'
        );

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<IBANCheckTransactionRequest',
            'entranceCode="IBAN-ENTRANCE-123"',
            '<IBAN>NL66ABNA4097012428</IBAN>',
            '<AssumedName>D.J.M.   Daan Jeroen Maarten Quackernaat</AssumedName>',
            '<DebtorReference>1234</DebtorReference>'
        );
    }
}
