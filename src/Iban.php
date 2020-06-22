<?php 
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Bluem\BluemPHP;


use Carbon\Carbon;



class IbanBluemRequest extends BluemRequest
{
	private $request_url_type = "icr";
    public $type_identifier = "createTransaction";   
    
    public $transaction_code = "INX";    

	public function TransactionType() : String
	{
        return "INX";
	}
}

