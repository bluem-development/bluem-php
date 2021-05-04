<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\BIC;

class PaymentsContext extends BluemContext
{
    public $debtorWalletElementName = "IDEAL";

    /**
     * PaymentsContext constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("BUNQNL2A", "bunq"),
                new BIC("HANDNL2A", "Handelsbanken"),
                new BIC("INGBNL2A", "ING"),
                new BIC("KNABNL2H", "Knab"),
                new BIC("MOYONL21", "Moneyou"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "SNS"),
                new BIC("TRIONL2U", "Triodos Bank"),
                new BIC("FVLBNL22", "Van Lanschot"),
                new BIC("REVOLT21", "Revolut"),
            ]
        );
    }

    public function getValidationSchema()
    {
        return parent::getValidationSchema() . 'EPayment.xsd';
    }
}
