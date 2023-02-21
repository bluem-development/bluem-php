<?php

namespace Bluem\BluemPHP\Responses;

use Bluem\BluemPHP\Helpers\BluemMaxAmount;

class MandateStatusBluemResponse extends StatusBluemResponse {
    public static $transaction_type = "EMandate";
    public static $response_primary_key = 'EMandateStatus';
    public static $error_response_type = 'EMandateErrorResponse';

    public function GetDebtorIBAN(): string {
        if ( $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN !== null ) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN . "";
        }

        return "";
    }

    public function GetDebtorBankID(): string {
        if ( $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID !== null ) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID . "";
        }

        return "";
    }

    /**
     * @throws Exception
     */
    public function GetMaximumAmount(): BluemMaxAmount {
        $acceptance_report = $this->getAcceptanceReport();
        if ( ! $acceptance_report ) {
            throw new Exception( "No acceptance report delivered" );
        }

        if ( isset( $acceptance_report->MaxAmount ) ) {
            return new BluemMaxAmount(
                (float) ( $acceptance_report->MaxAmount . "" ),
                'EUR'
            );
        }

        return new BluemMaxAmount(
            0.0,
            'EUR'
        );
    }

    private function getAcceptanceReport() {

        if ( isset( $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport ) ) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport;
        }

        return false;

    }

    public function GetDebtorAccountName()
    {
        if ($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName !== null) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName . "";
        }

        return null;
    }
}
