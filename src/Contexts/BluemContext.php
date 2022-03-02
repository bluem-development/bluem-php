<?php

namespace Bluem\BluemPHP\Contexts;

// @todo: Add Paypal context and distinguish between iDeal and Paypal contexts based on debtorWalletElementName
// in paypal, BICs don't matter
// so there should be an intermediate BluemBankContext
// and BluemPayPalContext that can then be extended by PaymentsPayPalContext and PaymentsBankContext
// and for Identity etc., if applicable, but we have to find out.

class BluemContext {
    public $BICs;

    /**
     * BluemContext constructor.
     *
     * @param array $BICs
     */
    public function __construct( array $BICs = [] ) {
        $this->BICs = $BICs;
    }

    public function getBICs(): array {
        return $this->BICs;
    }

    public function getBICCodes(): array {
        $codes = [];
        foreach ( $this->BICs as $BIC ) {
            $codes[] = $BIC->issuerID;
        }

        return $codes;
    }

    public function getValidationSchema(): string {
        return __DIR__ . '/../../validation/';
    }
}
