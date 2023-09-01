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
        return $this->getKeyFromIBANCheckResult("IBANResult", "IBANCheckResult");
    }

    public function GetNameResult(): string
    {
        return $this->getKeyFromIBANCheckResult("NameResult", "IBANCheckResult");
    }

    public function GetSuggestedName(): string
    {
        return $this->getKeyFromIBANCheckResult("SuggestedName", "IBANCheckResult");
    }

    public function GetAccountStatus(): string
    {
        return $this->getKeyFromIBANCheckResult("AccountStatus", "IBANCheckResult");
    }

    public function GetAccountType(): string
    {
        return $this->getKeyFromIBANCheckResult("AccountType", "AccountDetails");
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
