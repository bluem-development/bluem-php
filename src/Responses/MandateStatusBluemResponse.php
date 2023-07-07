<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

use Bluem\BluemPHP\Helpers\BluemCurrency;
use Bluem\BluemPHP\Helpers\BluemMaxAmount;
use Exception;
use SebastianBergmann\Template\RuntimeException;
use SimpleXMLElement;

class MandateStatusBluemResponse extends StatusBluemResponse
{
    public static string $transaction_type = "EMandate";
    public static string $response_primary_key = 'EMandateStatus';
    public static string $error_response_type = 'EMandateErrorResponse';

    public function GetDebtorIBAN(): string
    {
        if ($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN !== null ) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorIBAN . "";
        }

        return "";
    }

    public function GetDebtorBankID(): string
    {
        if ($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID !== null ) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorBankID . "";
        }

        return "";
    }

    /**
     * @throws Exception
     */
    public function GetMaximumAmount(): BluemMaxAmount
    {
        $acceptance_report = $this->getAcceptanceReport();
        if (! $acceptance_report ) {
            throw new RuntimeException("No acceptance report delivered");
        }

        // @todo: get currency from report
        $currency = new BluemCurrency();

        if ($acceptance_report->MaxAmount !== null ) {
            $maxAmount = (float) ( $acceptance_report->MaxAmount . "" );

            return new BluemMaxAmount(
                $maxAmount,
                $currency
            );
        }

        return new BluemMaxAmount(0.0, $currency);
    }

    private function getAcceptanceReport(): ?SimpleXMLElement
    {
        return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport ?? null;
    }

    public function GetDebtorAccountName(): ?string
    {
        if ($this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName !== null) {
            return $this->EMandateStatusUpdate->EMandateStatus->AcceptanceReport->DebtorAccountName . "";
        }

        return '';
    }
}
