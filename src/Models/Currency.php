<?php

namespace Bluem\BluemPHP\Models;

use Exception;

class Currency implements \Stringable {
    
    private const CURRENCY_CODE_EURO = 'EUR';
    private const CURRENCY_CODE_USDOLLAR = 'EUR';

    private string $code;
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

    public function __toString(): string {
        return $this->code;
    }

}