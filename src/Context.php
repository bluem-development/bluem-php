<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Exception;

class BluemContext
{
    public $BICs;

    /**
     * Constructor
     *
     * @param [type] $BICs
     */
    public function __construct($BICs)
    {
        $this->BICs = $BICs;
    }
}




class PaymentsContext extends BluemContext
{
    /**
     * Constructor
     *
     * @param [type] $BICs
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
                new BIC("FVLBNL22", "Van Lanschot")
            ]
        );
    }

    public function getBICs()
    {
        return $this->BICs;
    }
}



class MandatesContext extends BluemContext
{
    private $possibleMandateTypes = ['CORE','B2B'];
    /**
     * Constructor
     *
     * @param [type] $BICs
     */
    public function __construct($type="CORE")
    {

        if (!in_array($type, $this->possibleMandateTypes)) {
            throw new Exception(
                "Unknown instrument code set as mandate type;
                should be either 'CORE' or 'B2B'"
            );
        }
        if($type =="CORE")
        {
            $BICs = [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("ASNBNL21", "ASN Bank"),
                new BIC("INGBNL2A", "ING"),
                new BIC("KNABNL2H", "Knab"),
                new BIC("RABONL2U", "Rabobank"),
                new BIC("RBRBNL21", "RegioBank"),
                new BIC("SNSBNL2A", "SNS"),
                new BIC("TRIONL2U", "Triodos Bank")
            ];
        } else {
            $BICs = [
                new BIC("ABNANL2A", "ABN AMRO"),
                new BIC("INGBNL2A", "ING"),
                new BIC("RABONL2U", "Rabobank")
            ];
        }
        parent::__construct(
            $BICs
        );
    }
}



class IdentityContext extends BluemContext
{
    /**
     * Constructor
     *
     * @param [type] $BICs
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
                new BIC("TRIONL2U", "Triodos Bank")
            ]
        );
    }
}

