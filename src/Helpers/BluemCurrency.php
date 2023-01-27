<?php

namespace Bluem\BluemPHP\Helpers;

use Exception;

class BluemCurrency implements \Stringable {

    /**
     * @var string
     */
    public $code;
    // @todo: add more allowed currencies based on XSD
    // or use regex [A-Z]{3}
    /**
     * @var string[]
     */
    private array $allowed_currencies = [ 'EUR', 'USD' ];

    /**
     * @throws Exception
     */
    public function __construct( string $code ) {

        if ( ! in_array( $code, $this->allowed_currencies ) ) {
            throw new Exception( 'Currency code $code not allowed' );
        }

        $this->code = $code;
    }

    public function __toString(): string {
        return $this->code;
    }

}