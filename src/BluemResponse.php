<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Exception;

/**
 *  BluemResponse
 */
class BluemResponse extends \SimpleXMLElement
{
    /**
     * Response Primary Key used to access the XML structure based on the specific type of response
     *
     * @var String
     */
    public static $response_primary_key;

    /** Transaction type used to differentiate the specific type of response
     *
     * @var String
     */
    public static $transaction_type;

    /** Error response type used to differentiate the specific type of response
     *
     * @var String
     */
    public static $error_response_type;

    /**
     * Return if the response is a successfull one, in boolean
     *
     * @return Bool
     */
    public function Status(): Bool
    {
        // $key =
        if (isset($this->{static::$error_response_type})) {
            return false;
        }
        return true;
    }

    public function ReceivedResponse()
    {
        return $this->Status();
    }

    /**
     * Return the error message, if there is one. Else return null
     *
     */
    public function Error()
    {
        if (isset($this->EMandateErrorResponse)) {
            return $this->EMandateErrorResponse->Error;
        }
        return null;
    }

    /**
     * Retrieve the generated Entrancecode enclosed in this response
     *
     * @return String
     */
    public function GetEntranceCode(): String
    {
        $attrs = $this->{$this->getParentXmlElement()}->attributes();

        if (!isset($attrs['entranceCode'])) {
            return null;
            // throw new \Exception("An error occured in reading the transaction response: no entrance code found.");
        }
        $entranceCode = $attrs['entranceCode'] . "";
        return $entranceCode;
    }

    protected function getParentXmlElement()
    {
        // overriden in children
        return; //static::$response_primary_key . "Response";
    }

    protected function getChildXmlElement()
    {
        return static::$response_primary_key;
    }


    // @todo only add this in transaction-based responses
    public function GetTransactionURL()
    {
    }
    public function GetTransactionID()
    {
    }
    public function GetStatusCode()
    {
    }
    public function GetIdentityReport()
    {
    }
}

/**
 * EMandateErrorResponse
 */
class ErrorBluemResponse
{
    private $error;

    public function __construct(String $error)
    {
        $this->error = $error;
    }

    public function Status(): Bool
    {
        return false;
    }

    public function ReceivedResponse()
    {
        return false;
    }
    public function Error()
    {
        return $this->error;
    }
}



class TransactionBluemResponse extends BluemResponse
{
    public function GetTransactionURL()
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionURL)) ? $this->{$this->getParentXmlElement()}->TransactionURL . "" : null;
    }

    public function GetTransactionID()
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionID)) ? $this->{$this->getParentXmlElement()}->TransactionID . "" : null;
    }

    protected function getParentXmlElement()
    {
        return static::$response_primary_key . "Response";
    }
}


class StatusBluemResponse extends BluemResponse
{
    public function GetStatusCode()
    {
        $parent_key = $this->getParentXmlElement();
        $child_key = $this->getChildXmlElement();

        if (isset($this->{$parent_key}->{$child_key}->Status)) {
            return $this->{$parent_key}->{$child_key}->Status . "";
        }
        return null;
    }

    protected function getParentXmlElement()
    {
        return static::$response_primary_key . "Update";
    }
}


class MandateTransactionBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Transaction";
    public static $error_response_type = "EMandate" . "ErrorResponse";

    // @todo Function to retrieve Mandate ID?
}

class MandateStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Status";
    public static $error_response_type = "EMandate" . "ErrorResponse";
    

    public function GetDebtorIBAN()
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN."";
        }
        return null;
    }
    public function GetDebtorBankID()
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID."";
        }
        return null;
    }
}

class PaymentTransactionBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "Payment";
    public static $response_primary_key = "Payment" . "Transaction";
    public static $error_response_type = "Payment" . "ErrorResponse";
}

class PaymentStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "Payment";
    public static $response_primary_key = "Payment" . "Status";
    public static $error_response_type = "Payment" . "ErrorResponse";
}


class IdentityStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "Identity";
    public static $response_primary_key = "Identity" . "Status";
    public static $error_response_type = "Identity" . "ErrorResponse";


    public function GetStatusCode()
    {
        if (isset($this->{$this->getParentXmlElement()}->Status)) {
            return $this->{$this->getParentXmlElement()}->Status . "";
        }
        return null;
    }


    public function GetIdentityReport()
    {
        if (isset($this->{$this->getParentXmlElement()}->IdentityReport)) {
            return $this->{$this->getParentXmlElement()}->IdentityReport;
        }
        return null;
    }

    public function GetAuthenticationAuthorityID()
    {
        if (isset($this->{$this->getParentXmlElement()}->AuthenticationAuthorityID)) {
            return $this->{$this->getParentXmlElement()}->AuthenticationAuthorityID . "";
        }
        return null;
    }
}
class IdentityTransactionBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "Identity";
    public static $response_primary_key = "Identity" . "Transaction";
    public static $error_response_type = "Identity" . "ErrorResponse";
}

class IBANNameCheckBluemResponse extends TransactionBluemResponse
{
    public static $transaction_type = "IBANCheckTransactionResponse";
    public static $response_primary_key = "IBANCheckTransaction";
    public static $error_response_type = "IBANCheckResult";

    public function GetIBANResult() {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->IBANResult)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->IBANResult . "";
        }

    }

    public function GetNameResult() {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->NameResult)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->NameResult . "";
        }
        return false;
    }
    public function GetAccountStatus() {
        if (isset($this->{$this->getParentXmlElement()}->IBANCheckResult->AccountStatus)) {
            return $this->{$this->getParentXmlElement()}->IBANCheckResult->AccountStatus . "";
        }
        return false;
    }

    public function GetAccountType() {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->AccountType)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->AccountType . "";
        }
        return false;
    }
    public function GetIsJointAccount() {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->IsJointAccount)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->IsJointAccount . "";
        }
        return false;
    }
    public function GetNumberOfAccountHolders() {
        if (isset($this->{$this->getParentXmlElement()}->AccountDetails->NumberOfAccountHolders)) {
            return $this->{$this->getParentXmlElement()}->AccountDetails->NumberOfAccountHolders . "";
        }
        return false;
    }
    public function GetCountryName() {
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
        <AssumedName>Zeland</AssumedName>
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