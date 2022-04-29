<?php
/*
 * (c) 2021 - Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Contexts;

use Bluem\BluemPHP\Helpers\BIC;
use Bluem\BluemPHP\Helpers\CoreBIC;
use Exception;

class MandatesContext extends BluemContext
{
    private const MANDATE_TYPE_CORE = 'CORE';
    private const MANDATE_TYPE_B2B = 'B2B';
    private const MANDATE_TYPE_BOTH = 'BOTH';
    
    /**
     * @var string
     */
    public $debtorWalletElementName = "INCASSOMACHTIGEN";
    
    /** @var string[]  */
    private $possibleMandateTypes = [ self::MANDATE_TYPE_B2B, self::MANDATE_TYPE_CORE, self::MANDATE_TYPE_BOTH ];
    

    /**
     * MandatesContext constructor.
     *
     * Note: B2B mandates = business eMandates
     *
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct( $type = self::MANDATE_TYPE_CORE ) {
        if ( ! in_array( $type, $this->possibleMandateTypes ) ) {
            throw new Exception(
                "Unknown instrument code set as mandate type;
                should be either 'CORE' or 'B2B'"
            );
        }
        
        $coreBics = [
            new BIC( "ABNANL2A", "ABN AMRO", self::MANDATE_TYPE_CORE ),
            new BIC( "ASNBNL21", "ASN Bank", self::MANDATE_TYPE_CORE ),
            new BIC( "INGBNL2A", "ING", self::MANDATE_TYPE_CORE ),
            new BIC( "KNABNL2H", "Knab", self::MANDATE_TYPE_CORE ),
            new BIC( "RABONL2U", "Rabobank", self::MANDATE_TYPE_CORE ),
            new BIC( "RBRBNL21", "RegioBank", self::MANDATE_TYPE_CORE ),
            new BIC( "SNSBNL2A", "SNS", self::MANDATE_TYPE_CORE ),
            new BIC( "TRIONL2U", "Triodos Bank", self::MANDATE_TYPE_CORE ),
        ];
        $b2bBics = [
            new BIC( "ABNANL2A", "ABN AMRO", self::MANDATE_TYPE_B2B ),
            new BIC( "INGBNL2A", "ING", self::MANDATE_TYPE_B2B ),
            new BIC( "RABONL2U", "Rabobank", self::MANDATE_TYPE_B2B ),
        ];
        
        if ( $type == self::MANDATE_TYPE_CORE ) {
            $BICs = $coreBics;
        } elseif($type==self::MANDATE_TYPE_B2B) {
            $BICs = $b2bBics;
        } elseif($type==self::MANDATE_TYPE_BOTH) { // both
            $BICs = array_merge($coreBics,$b2bBics);
        }

        parent::__construct( $BICs );
    }

    public function getValidationSchema(): string {
        return parent::getValidationSchema() . 'EMandate.xsd';
    }
}
