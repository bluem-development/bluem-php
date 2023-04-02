<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;

class IdentityContext extends BluemContext {
    public const PAYMENT_METHOD_IDIN = 'IDIN';

    public $debtorWalletElementName = "IDIN";

    /**
     * @var array
     */
    private array $paymentMethodDetails;

    /**
     * IdentityContext constructor.
     */
    public function __construct() {
        parent::__construct(
            [
                new BIC( "ABNANL2A", "ABN AMRO" ),
                new BIC( "ASNBNL21", "ASN Bank" ),
                new BIC( "BUNQNL2A", "bunq" ),
                new BIC( "INGBNL2A", "ING" ),
                new BIC( "RABONL2U", "Rabobank" ),
                new BIC( "RBRBNL21", "RegioBank" ),
                new BIC( "SNSBNL2A", "SNS" ),

                // Triodos Bank, BIC TRIONL2U no longer supported as of 1 june 2021.
            ]
        );
    }

    public function getValidationSchema(): string {
        return parent::getValidationSchema() . 'EIdentity.xsd';
    }

    public function isIDIN(): bool
    {
        return $this->debtorWalletElementName === self::PAYMENT_METHOD_IDIN;
    }

    public function addPaymentMethodDetails(array $details = [])
    {
        $validationErrors = $this->validateDetails($details);
        if ($validationErrors !== [] ) {
            throw new RuntimeException('Invalid details given: '. implode(', ', $validationErrors));
        }

        $this->paymentMethodDetails = $details;
    }

    private function validateDetails(array $details = []): array
    {
        if ($this->isIDIN()) {
            // no validation yet
        }

        return [];
    }

    public function getPaymentDetail(string $key)
    {
        return $this->paymentMethodDetails[$key] ?? null;
    }
}
