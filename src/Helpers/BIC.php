<?php
/*
 * (c) 2021 - Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Helpers;

/**
 * Definition for a BIC/Swift Code
 */
class BIC
{

    /**
     * The official ID of the issuer (bank)
     *
     * @var String
     */
    public $issuerID;

    /**
     * The name of the issuer (bank)
     *
     * @var String
     */
    public $issuerName;

    /**
     * Create a new BIC Definition for a bank (Issuer)
     *
     * @param String $issuerID   The issuer ID
     * @param String $issuerName The issuer name
     */
    public function __construct(
        String $issuerID,
        String $issuerName
    ) {
        $this->issuerID = $issuerID;
        $this->issuerName = $issuerName;
        
        // @todo: add additional validation for ID and Names
    }
}
