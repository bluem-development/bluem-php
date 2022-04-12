<?php

namespace Bluem\BluemPHP\Responses;

class MandateStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Status";
    public static $error_response_type = "EMandate" . "ErrorResponse";


    public function GetDebtorIBAN()
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN . "";
        }

        return null;
    }

    public function GetDebtorBankID()
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID . "";
        }

        return null;
    }

    public function GetDebtorAccountName()
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName . "";
        }

        return null;
    }
}
