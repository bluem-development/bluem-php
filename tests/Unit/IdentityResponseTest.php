<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Responses\IdentityStatusBluemResponse;
use Bluem\BluemPHP\Responses\IdentityTransactionBluemResponse;

final class IdentityResponseTest extends ResponseTestCase
{
    public function testIdentityTransactionResponseReadsTransactionData(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IdentityInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <IdentityTransactionResponse entranceCode="showConsumerGuiMYOWNENTRANCECODE77128">
        <TransactionURL>https://test.viamijnbank.net/identity/transaction/1234abcdef</TransactionURL>
        <TransactionID>1234abcdef</TransactionID>
        <DebtorReference>1234</DebtorReference>
    </IdentityTransactionResponse>
</IdentityInterface>
XML;

        $response = $this->loadXmlResponse($xml, IdentityTransactionBluemResponse::class);

        self::assertSame('https://test.viamijnbank.net/identity/transaction/1234abcdef', $response->GetTransactionURL());
        self::assertSame('1234abcdef', $response->GetTransactionID());
        self::assertSame('1234', $response->GetDebtorReference());
    }

    public function testIdentityStatusResponseReadsIdentityReport(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IdentityInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <IdentityStatusUpdate entranceCode="showConsumerGuiMYOWNENTRANCECODE77128">
        <AuthenticationAuthorityID>AUTH-001</AuthenticationAuthorityID>
        <Status>Success</Status>
        <IdentityReport>
            <ReportStatus>Verified</ReportStatus>
            <CustomerName>INSERT_VARIABLE_CUSTOMER_NAME_HERE</CustomerName>
        </IdentityReport>
    </IdentityStatusUpdate>
</IdentityInterface>
XML;

        $response = $this->loadXmlResponse($xml, IdentityStatusBluemResponse::class);

        self::assertSame('AUTH-001', $response->GetAuthenticationAuthorityID());
        self::assertNotNull($response->GetIdentityReport());
        self::assertSame('Verified', (string) $response->GetIdentityReport()->ReportStatus);
    }

    public function testIdentityStatusResponseReturnsNullWhenIdentityReportIsMissing(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IdentityInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="StatusUpdate" version="1.0">
    <IdentityStatusUpdate entranceCode="showConsumerGuiMYOWNENTRANCECODE77128">
        <AuthenticationAuthorityID>AUTH-002</AuthenticationAuthorityID>
        <Status>Success</Status>
    </IdentityStatusUpdate>
</IdentityInterface>
XML;

        $response = $this->loadXmlResponse($xml, IdentityStatusBluemResponse::class);

        self::assertSame('AUTH-002', $response->GetAuthenticationAuthorityID());
        self::assertNull($response->GetIdentityReport());
    }
}
