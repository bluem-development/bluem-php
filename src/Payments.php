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
    protected $xmlInterfaceName = "EPaymentInterface";

    public $request_url_type = "pr";
    public $type_identifier = "createTransaction";
    public $transaction_code = "PSX";

    public function TransactionType(): String
    {
        return "PSX";
    }


    function __construct($config, $transactionID, $expected_return = "", $entranceCode = "")
    {
        parent::__construct($config, $expected_return, $entranceCode);
        $this->type_identifier = "requestStatus";

        $this->transactionID = $transactionID;
    }

    public function XmlString()
    {

        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'PaymentStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );

        /*            
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<EPaymentInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="StatusRequest"
    mode="direct" senderID="'.$this->senderID.'" version="1.0" createDateTime="'.$this->createDateTime.'"
    messageCount="1">
    <PaymentStatusRequest entranceCode="'.$this->entranceCode.'">
        <TransactionID>'.$this->transactionID.'</TransactionID>
    </PaymentStatusRequest>
</EPaymentInterface>';
*/
    }
}


class PaymentBluemRequest extends BluemRequest
{
    private $xmlInterfaceName = "EPaymentInterface";
    public $request_url_type = "pr";
    public $type_identifier = "createTransaction";
    public $transaction_code = "PTX";
    
    public function TransactionType(): String
    {
        return "PTX";
    }

    public function __construct(
        $config, 
        $description,
        $debtorReference,
        $amount,
        $dueDateTime =null,
        $currency =null,
        $transactionID=null, 
        String $expected_return="none")
        {
            parent::__construct($config,"",$expected_return);
        
        // TODO: fill all the rquired fields
        $this->description = $description;
        
        //  Currency EUR
        if(is_null($currency)) {
            $this->currency = "EUR";
        } else {
            $this->currency = $currency;
        }

        // DueDateTime :  2017-09-09T23:59:59.999Z
        if(is_null($dueDateTime)) {
            $this->dueDateTime = Carbon::now()->addDays(1)->toDateTimeLocalString() . ".000Z";;//->format('Y-m-d\TH:i:s').'.000Z';   //->format("Y-m-d")."T23:59:59Z";
        } else {
            $this->dueDateTime = Carbon::parse($dueDateTime)->toDateTimeLocalString() . ".000Z";;//format('Y-m-d\TH:i:s').'.000Z';   
        }
        // $this->dueDateTime .= '...';
// 
        $this->debtorReference = $debtorReference;
        $this->amount = str_replace(',','.',$amount);

        if(strpos($this->amount,'.')==false) {
            $this->amount .= '.00';
        }


        $this->transactionID = $transactionID;
        $this->debtorReturnURL = $config->merchantReturnURLBase."?entranceCode={$this->entranceCode}&amp;transactionID={$this->transactionID}";
        // note! different variable name in config
        // added entranceCode as well, useful. Defined in generic bluem request class

        $this->paymentReference="{$this->debtorReference}{$this->transactionID}";
    }


    public function XmlString()
    {

        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'PaymentTransactionRequest',
                '<PaymentReference>' . $this->paymentReference . '</PaymentReference>
            <DebtorReference>' . $this->debtorReference . '</DebtorReference>
            <Description>' . $this->description . '</Description>
            <Currency>' . $this->currency . '</Currency>
            <Amount>' . $this->amount . '</Amount>
            <DueDateTime>' . $this->dueDateTime . '</DueDateTime>
            <DebtorReturnURL automaticRedirect="1">' . str_replace('&','&amp;',$this->debtorReturnURL) . '</DebtorReturnURL>',
            [
                'documentType'=>"PayRequest",
                'sendOption'=>"none",
                'language'=>"nl"
            ]
            )
        );
    }

    
}
