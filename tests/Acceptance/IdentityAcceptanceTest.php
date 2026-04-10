<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Acceptance;

use Bluem\BluemPHP\Requests\IdentityBluemRequest;

final class IdentityAcceptanceTest extends AcceptanceTestCase
{
    public function testIdentityRequestXmlUsesSandboxSampleData(): void
    {
        $config = $this->createConfiguration('BluemIdentity');

        $request = new IdentityBluemRequest(
            $config,
            'showConsumerGuiMYOWNENTRANCECODE77128',
            'success',
            ['AddressRequest', 'BirthDateRequest'],
            'Beschrijving',
            '1234',
            'http://localhost/code/etc/'
        );

        $request->selectDebtorWallet('INGBNL2A');
        $request->enableStatusGUI();

        $xml = $request->XmlString();

        $this->assertXmlContains(
            $xml,
            '<IdentityTransactionRequest',
            'entranceCode="showConsumerGuiMYOWNENTRANCECODE77128"',
            '<AddressRequest action="request"/>',
            '<BirthDateRequest action="request"/>',
            '<Description>Beschrijving</Description>',
            '<DebtorReference>1234</DebtorReference>',
            '<DebtorReturnURL automaticRedirect="1">http://localhost/code/etc/?debtorReference=1234</DebtorReturnURL>',
        );
    }
}
