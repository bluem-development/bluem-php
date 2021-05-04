<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\BIC;

class IdentityContext extends BluemContext
{
    public $debtorWalletElementName = "IDIN";

    /**
     * IdentityContext constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("BUNQNL2A", "bunq"),
                new BIC("INGBNL2A", "ING"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "SNS"),
                new BIC("TRIONL2U", "Triodos Bank"),
            ]
        );
    }

    public function getValidationSchema()
    {
        return parent::getValidationSchema() . 'EIdentity.xsd';
    }
}
