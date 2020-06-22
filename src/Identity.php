<?php 
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Bluem\BluemPHP;


use Carbon\Carbon;



class IdentityBluemRequest extends BluemRequest
{
	private $request_url_type = "ir";
    public $type_identifier = "createTransaction";   
    public $transaction_code = "ITX";    
    
	public function TransactionType() : String
	{
        return "ITX";
    }
	
}


class IdentityStatusBluemRequest extends BluemRequest
{
    public $request_url_type = "ir";
    public $type_identifier = "requestStatus"; 
    public $transaction_code = "ISX";    
    public function TransactionType() : String
	{
        return "ISX";
    }
}