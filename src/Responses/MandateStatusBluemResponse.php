<?php

namespace Bluem\BluemPHP\Responses;

class MandateStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Status";
    public static $error_response_type = "EMandate" . "ErrorResponse";


    public function GetDebtorIBAN(): ?string
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN . "";
        }

        return null;
    }

    public function GetDebtorBankID(): ?string
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID . "";
        }

        return null;
    }
}
