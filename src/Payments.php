<?php 
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Bluem\BluemPHP;


use Carbon\Carbon;

class PaymentStatusBluemRequest extends BluemRequest
{
    private $request_url_type = "pr";
    public $type_identifier = "createTransaction";   
    public $transaction_code = "PSX";    

	public function TransactionType() : String
	{
        return "PSX";
    }	
}


class PaymentBluemRequest extends BluemRequest
{
    private $request_url_type = "pr";
    public $type_identifier = "requestStatus"; 
    public $transaction_code = "PTX";    

	public function TransactionType() : String
	{
        return "PTX";
    }

}
