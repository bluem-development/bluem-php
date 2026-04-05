<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Responses\IBANNameCheckBluemResponse;

final class IbanResponseTest extends ResponseTestCase
{
    public function testIbanResponseKnownResultExposesAllFields(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IBANCheckInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <IBANCheckTransactionResponse entranceCode="20260405095326910">
        <IBANCheckResult>
            <IBANResult>KNOWN</IBANResult>
            <NameResult>MATCH</NameResult>
            <SuggestedName>D.J.M. Daan Jeroen Maarten Quackernaat</SuggestedName>
            <AccountStatus>OPEN</AccountStatus>
        </IBANCheckResult>
        <AccountDetails>
            <AccountType>PERSONAL</AccountType>
            <IsJointAccount>false</IsJointAccount>
            <NumberOfAccountHolders>1</NumberOfAccountHolders>
            <CountryName>Netherlands</CountryName>
        </AccountDetails>
    </IBANCheckTransactionResponse>
</IBANCheckInterface>
XML;

        $response = $this->loadXmlResponse($xml, IBANNameCheckBluemResponse::class);

        self::assertSame('KNOWN', $response->GetIBANResult());
        self::assertSame('MATCH', $response->GetNameResult());
        self::assertSame('D.J.M. Daan Jeroen Maarten Quackernaat', $response->GetSuggestedName());
        self::assertSame('OPEN', $response->GetAccountStatus());
        self::assertSame('PERSONAL', $response->GetAccountType());
        self::assertSame('false', $response->GetIsJointAccount());
        self::assertSame('1', $response->GetNumberOfAccountHolders());
        self::assertSame('Netherlands', $response->GetCountryName());
    }

    public function testIbanResponseInvalidAndUnavailableStatesRemainParsable(): void
    {
        $invalidXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IBANCheckInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <IBANCheckTransactionResponse entranceCode="20260405095326910">
        <IBANCheckResult>
            <IBANResult>INVALID</IBANResult>
        </IBANCheckResult>
    </IBANCheckTransactionResponse>
</IBANCheckInterface>
XML;

        $unavailableXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<IBANCheckInterface createDateTime="2026-04-05T00:00:00Z" messageCount="1" mode="direct" senderID="S001" type="TransactionRequest" version="1.0">
    <IBANCheckTransactionResponse entranceCode="20260405095326910">
        <IBANCheckResult>
            <IBANResult>SERVICE_TEMPORARILY_NOT_AVAILABLE</IBANResult>
        </IBANCheckResult>
    </IBANCheckTransactionResponse>
</IBANCheckInterface>
XML;

        $invalidResponse = $this->loadXmlResponse($invalidXml, IBANNameCheckBluemResponse::class);
        $unavailableResponse = $this->loadXmlResponse($unavailableXml, IBANNameCheckBluemResponse::class);

        self::assertSame('INVALID', $invalidResponse->GetIBANResult());
        self::assertSame('SERVICE_TEMPORARILY_NOT_AVAILABLE', $unavailableResponse->GetIBANResult());
    }
}
