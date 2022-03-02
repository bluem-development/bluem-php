<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;

class IdentityContext extends BluemContext {
    public $debtorWalletElementName = "IDIN";

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
}
