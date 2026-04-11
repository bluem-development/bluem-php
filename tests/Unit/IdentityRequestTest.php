<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Bluem;
use Exception;

final class IdentityRequestTest extends RequestTestCase
{
    public function testCreateIdentityRequestMatchesExampleShape(): void
    {
        $bluem = $this->createBluem();

        $request = $bluem->CreateIdentityRequest(
            ['BirthDateRequest', 'AddressRequest'],
            'Test identity',
            '1234',
            'IDENTITY-ENTRANCE-123',
            'https://yourdomain.com/integration/identity.php?action=callback'
        );

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<IdentityTransactionRequest',
            'entranceCode="IDENTITY-ENTRANCE-123"',
            '<BirthDateRequest action="request"/>',
            '<AddressRequest action="request"/>',
            '<CustomerIDRequest action="skip"/>',
            '<Description>Test identity</Description>',
            '<DebtorReference>1234</DebtorReference>'
        );

        self::assertStringContainsString(
            '<DebtorReturnURL automaticRedirect="1">https://yourdomain.com/integration/identity.php?action=callback?debtorReference=1234</DebtorReturnURL>',
            $xml
        );
    }

    public function testCreateIdentityRequestRequiresReturnUrl(): void
    {
        $bluem = $this->createBluem();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Debtor return URL is required');

        $bluem->CreateIdentityRequest(
            ['BirthDateRequest', 'AddressRequest'],
            'Test identity',
            '1234',
            'IDENTITY-ENTRANCE-123'
        );
    }

    private function createBluem(): Bluem
    {
        return new Bluem($this->createConfiguration());
    }
}

