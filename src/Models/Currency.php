<?php

namespace Bluem\BluemPHP\Models;

use Exception;

class Currency {
    
    private const CURRENCY_CODE_EURO = 'EUR';
    private const CURRENCY_CODE_USDOLLAR = 'EUR';

    /**
     * @var string
     */
    private $code;
    // or use regex [A-Z]{3}
    
    /**
     * @var string[]
     */
    private const ALLOWED_CURRENCIES = [ 
        self::CURRENCY_CODE_EURO, 
        self::CURRENCY_CODE_USDOLLAR 
    // @todo: add more allowed currencies based on XSD
    ];

    /**
     * @param string $code
     *
     * @throws Exception
     */
    public function __construct( string $code = '' ) {

        if($code =='') {
            $code = self::CURRENCY_CODE_EURO;
        }
        if ( ! in_array( $code, self::ALLOWED_CURRENCIES ) ) {
            throw new Exception( 'Currency code not allowed' );
        }

        $this->code = $code;
    }

    public function __toString() {
        return $this->code;
    }

}