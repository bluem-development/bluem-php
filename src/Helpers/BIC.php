<?php
/*
 * (c) 2022 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Helpers;

/**
 * Definition for a BIC/Swift Code
 */
class BIC {

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
     * Allowing possible override the local instrument code
     *
     * @var String
     */
    public $localInstrumentCode ='';

    /**
     * Create a new BIC Definition for a bank (Issuer)
     *
     * @param String $issuerID The issuer ID
     * @param String $issuerName The issuer name
     * @param String $localInstrumentCode The local instrument code override (optional)
     */
    public function __construct(
        string $issuerID,
        string $issuerName,
        string $overrideLocalInstrumentCode = ''
    ) {
        $this->issuerID   = $issuerID;
        $this->issuerName = $issuerName;

        if(!empty($overrideLocalInstrumentCode)) {
            $this->localInstrumentCode = $overrideLocalInstrumentCode;
            
            $this->issuerName = $this->issuerName . ' ' 
                                .($this->localInstrumentCode =="B2B") ? "(Zakelijk)" :"";
        }
        
        // @todo: add additional validation for ID and Names
    }
}

