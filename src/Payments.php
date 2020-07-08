<?php
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;


use Carbon\Carbon;

class PaymentStatusBluemRequest extends BluemStatusRequest
{
    protected $xmlInterfaceName = "EPaymentInterface";

    private $request_url_type = "pr";
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
    private $request_url_type = "pr";
    public $type_identifier = "requestStatus";
    public $transaction_code = "PTX";

    public function TransactionType(): String
    {
        return "PTX";
    }

    public function __construct()
    {
// TODO: fill all the rquired fields
        //  Currency EUR
        $this->currency = "EUR";
        // DueDateTime :  2017-09-09T23:59:59.999Z
        $this->dueDateTime = "";

        // $this->debtorReference = ;
        // $this->description = ;
        // $this->amount = ;
        // $this->debtorReturnURL = ;
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
            <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>',
            [
                'documentType'=>"PayRequest",
                'sendOption'=>"none",
                'language'=>"nl"
            ]
            )
        );
    }

    
}
