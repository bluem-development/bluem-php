<?php

namespace Bluem\BluemPHP\Responses;

use Bluem\BluemPHP\Helpers\BluemMaxAmount;

class MandateStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "EMandate";
    public static $response_primary_key = "EMandate" . "Status";
    public static $error_response_type = "EMandate" . "ErrorResponse";
    
    public function GetDebtorIBAN(): string
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN . "";
        }
        return "";
    }

    public function GetDebtorBankID(): string
    {
        if (isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID . "";
        }
        return "";
    }


    private function getAcceptanceReport() {

        if(isset($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport)) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport;
        }
        return false;

    }


    /**
     * @return BluemMaxAmount
     * @throws Exception
     */
    public function GetMaximumAmount(): BluemMaxAmount {
        $acceptance_report = $this->getAcceptanceReport();
        if(!$acceptance_report) {
            throw new Exception("No acceptance report delivered");
        }

        if (isset($acceptance_report->MaxAmount)) {
            return new BluemMaxAmount(
                (float) ($acceptance_report->MaxAmount . ""),
                'EUR'
            );
        }
        return new BluemMaxAmount(
            0.0,
            'EUR'
        );
    }
}
