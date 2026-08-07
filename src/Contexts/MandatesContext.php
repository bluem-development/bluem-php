<?php

declare(strict_types=1);

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;
use Exception;
use RuntimeException;

class MandatesContext extends BluemContext
{
    public const PAYMENT_METHOD_MANDATE = 'INCASSOMACHTIGEN';

    public string $debtorWalletElementName = "INCASSOMACHTIGEN";

    private array $_possibleMandateTypes = ['CORE', 'B2B'];

    private array $paymentMethodDetails;

    /**
     * MandatesContext constructor.
     *
     * Note: B2B mandates = business eMandates
     *
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct($type = "CORE")
    {
        if (! in_array($type, $this->_possibleMandateTypes)) {
            throw new Exception(
                "Unknown instrument code set as mandate type;
                should be either 'CORE' or 'B2B'"
            );
        }

        if ($type === "CORE") {
            $BICs = [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("INGBNL2A", "ING"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "ASN voorheen SNS"),
                new BIC("TRIONL2U", "Triodos Bank"),
                new BIC("BUNQNL2A", "bunq"),
                new BIC("KNABNL2H", "Knab"),
                new BIC("FVLBNL22", "Van Lanschot"),
                new BIC("REVOLT21", "Revolut"),
                new BIC("NTSBDEB1", "N26"),
                new BIC("OTHERBANK", "Andere bank"),
            ];
        } else {
            $BICs = [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("INGBNL2A", "ING"),
                new BIC("RABONL2U", "Rabobank"),
            ];
        }

        parent::__construct($BICs);
    }

    #[\Override]
    public function getValidationSchema(): string
    {
        return parent::getValidationSchema() . 'EMandate.xsd';
    }

    public function isMandate(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_MANDATE;
    }

    public function addPaymentMethodDetails(array $details = []): void
    {
        $validationErrors = $this->validateDetails();
        if ($validationErrors !== []) {
            throw new RuntimeException('Invalid details given: ' . implode(', ', $validationErrors));
        }

        $this->paymentMethodDetails = $details;
    }

    private function validateDetails(): array
    {
        if ($this->isMandate()) {
            // no validation yet
        }
        return [];
    }

    public function getPaymentDetail(string $key)
    {
        return $this->paymentMethodDetails[$key] ?? null;
    }
}
