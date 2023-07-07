<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

use SimpleXMLElement;

class IBANNameCheckBluemResponse extends TransactionBluemResponse
{
    public static string $transaction_type = "IBANCheckTransactionResponse";
    public static string $response_primary_key = "IBANCheckTransaction";
    public static ?string $error_response_type = "IBANCheckResult";

    private function getIBANCheckResultObject($parentObjectKey = "IBANCheckResult"): ?SimpleXMLElement
    {
        $parent = $this->getParentElement();

        if ($parent && !empty($parent->{$parentObjectKey})) {
            return $parent->{$parentObjectKey};
        }

        return null;
    }

    private function getKeyFromIBANCheckResult(string $key, string $parentObjectKey = null): string
    {
        $result = $this->getIBANCheckResultObject($parentObjectKey);

        if ($result && !empty($result->$key)) {
            return $result->$key . "";
        }

        return '';
    }


    public function GetIBANResult(): string
    {
        return $this->getKeyFromIBANCheckResult("IBANResult");
    }

    public function GetNameResult(): string
    {
        return $this->getKeyFromIBANCheckResult("NameResult");
    }

    public function GetSuggestedName(): string
    {
        return $this->getKeyFromIBANCheckResult("SuggestedName");
    }

    public function GetAccountStatus(): string
    {
        return $this->getKeyFromIBANCheckResult("AccountStatus");
    }

    public function GetAccountType(): string
    {
        return $this->getKeyFromIBANCheckResult("AccountStatus", "AccountDetails");
    }

    public function GetIsJointAccount(): string
    {
        return $this->getKeyFromIBANCheckResult("IsJointAccount", "AccountDetails");
    }

    public function GetNumberOfAccountHolders(): string
    {
        return $this->getKeyFromIBANCheckResult("NumberOfAccountHolders", "AccountDetails");
    }

    public function GetCountryName(): string
    {
        return $this->getKeyFromIBANCheckResult("CountryName", "AccountDetails");
    }
}

/* Response:
 *
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<IBANCheckInterface mode="direct" senderID="S1018" version="1.0" createDateTime="2019-09-09T08:43:58.022Z" messageCount="1" type="TransactionResponse">
    <IBANCheckTransactionResponse entranceCode="S101820190909084357980">
        <IBAN>NL59INGB0748545824</IBAN>
        <AssumedName>Zeeland</AssumedName>
        <DebtorReference>12345678</DebtorReference>
        <IBANCheckResult>
            <IBANResult>KNOWN</IBANResult>
            <NameResult>MISTYPED</NameResult>
            <SuggestedName>Naar Zeeland</SuggestedName>
            <AccountStatus>ACTIVE</AccountStatus>
        </IBANCheckResult>
        <AccountDetails>
            <AccountType>NATURAL_PERSON</AccountType>
            <IsJointAccount>true</IsJointAccount>
            <NumberOfAccountHolders>2</NumberOfAccountHolders>
            <CountryName>Netherlands</CountryName>
        </AccountDetails>
    </IBANCheckTransactionResponse>
</IBANCheckInterface>
 *
 */
