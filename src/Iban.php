<?php 

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;


class IbanBluemRequest extends BluemRequest
{
	private $xmlInterfaceName = "IBANCheckInterface";

	public $request_url_type = "icr";
    public $typeIdentifier = "createTransaction";   
    
    public $transaction_code = "INX";    

	public function TransactionType() : String
	{
        return "INX";
	}

	
    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IBANCheckTransactionRequest',
                '',    // onbekend
                [
                    // onbekend
                ]
            )
        );
    }
}
