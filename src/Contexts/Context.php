<?php

namespace Bluem\BluemPHP\Contexts;

// @todo: Add Paypal context and distinguish between iDeal and Paypal contexts based on debtorWalletElementName
// in paypal, issuers don't matter
// so there should be an intermediate BluemBankContext
// and BluemPayPalContext that can then be extended by PaymentsPayPalContext and PaymentsBankContext
// and for Identity etc., if applicable, but we have to find out.


// @todo: consider making this class static?

use Bluem\BluemPHP\Interfaces\ContextInterface;

class Context implements ContextInterface {

    public $issuers;

    /**
     * BluemContext constructor.
     *
     * @param array $issuers
     */
    public function __construct( array $issuers = [] ) {
        $this->issuers = $issuers;
    }

    public function getIssuers(): array {
        return $this->issuers;
    }

    public function getBICCodes(): array {
        $codes = [];
        foreach ( $this->issuers as $BIC ) {
            $codes[] = $BIC->issuerID;
        }

        return $codes;
    }

    public function getValidationSchema(): string {
        return __DIR__ . '/../../validation/';
    }
    
    public function getDebtorWalletElementName(): string {
        return '';
    }
}
