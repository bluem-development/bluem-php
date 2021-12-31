<?php
namespace Bluem\BluemPHP\Helpers;

use Bluem\BluemPHP\Bluem;
use Bluem\BluemPHP\Helpers\BluemCurrency;
class BluemMaxAmount {

    /**
     * @var \Bluem\BluemPHP\Helpers\BluemCurrency 
     */
    public $currency;
    
    /**
     * @var float 
     */
    public $amount;

    /**
     * @param float $amount
     * @param string $currency
     */
    function __construct(
        float $amount,
        string $currency
    ) {
        // @todo: validate the amount to be nonnegative and have a maximum, see xsd
        $this->amount = $amount;
        
        $this->currency = new BluemCurrency($currency);
    }
    
    public function __toString(): string {
        return $this->amount;
    }
    
}