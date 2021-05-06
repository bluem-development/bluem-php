<?php

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\BIC;
use Exception;

class MandatesContext extends BluemContext
{
    public $debtorWalletElementName = "INCASSOMACHTIGEN";

    private $_possibleMandateTypes = [ 'CORE', 'B2B' ];

    /**
     * MandatesContext constructor.
     *
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct($type = "CORE")
    {
        if (!in_array($type, $this->_possibleMandateTypes)) {
            throw new Exception(
                "Unknown instrument code set as mandate type;
                should be either 'CORE' or 'B2B'"
            );
        }
        if ($type == "CORE") {
            $BICs = [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("INGBNL2A", "ING"),
                new BIC("KNABNL2H", "Knab"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "SNS"),
                new BIC("TRIONL2U", "Triodos Bank"),
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

    public function getValidationSchema()
    {
        return parent::getValidationSchema() . 'EMandate.xsd';
    }
}
