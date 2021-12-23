<?php

namespace Bluem\BluemPHP\Responses;

class IBANNameCheckBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "IBANCheckTransactionResponse";
    public static $response_primary_key = "IBANCheckTransaction";
    public static $error_response_type = "IBANCheckResult";

    public function GetIBANResult()
    {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->IBANResult)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->IBANResult . "";
        }

        return false;
    }

    public function GetNameResult()
    {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->NameResult)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->NameResult . "";
        }

        return false;
    }

    public function GetSuggestedName()
    {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->SuggestedName)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->SuggestedName . "";
        }

        return false;
    }

    public function GetAccountStatus()
    {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->AccountStatus)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->AccountStatus . "";
        }

        return false;
    }

    public function GetAccountType()
    {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->AccountType)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->AccountType . "";
        }

        return false;
    }

    public function GetIsJointAccount()
    {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->IsJointAccount)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->IsJointAccount . "";
        }

        return false;
    }

    public function GetNumberOfAccountHolders()
    {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->NumberOfAccountHolders)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->NumberOfAccountHolders . "";
        }

        return false;
    }

    public function GetCountryName()
    {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->CountryName)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->CountryName . "";
        }

        return false;
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
