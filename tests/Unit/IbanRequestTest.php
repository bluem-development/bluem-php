<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;

final class IbanRequestTest extends RequestTestCase
{
    public function testCreateIbanNameCheckRequestMatchesExampleShape(): void
    {
        $bluem = $this->createBluem();

        $request = $bluem->CreateIBANNameCheckRequest(
            'NL66 ABNA 4097 0124 28',
            '  D. Jeroen Maarten  ',
            '1234'
        );

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<IBANCheckTransactionRequest',
            '<IBAN>NL66ABNA4097012428</IBAN>',
            '<AssumedName>D. Jeroen Maarten</AssumedName>',
            '<DebtorReference>1234</DebtorReference>'
        );
    }

    private function createBluem(): Bluem
    {
        return new Bluem($this->createConfiguration());
    }
}

