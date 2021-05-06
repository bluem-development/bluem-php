<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;

class PaymentStatusBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "EPaymentInterface";

    public $request_url_type = "pr";
    public $typeIdentifier = "requestStatus";
    public $transaction_code = "PSX";

    public function __construct(
        $config,
        $transactionID,
        $expected_return = "",
        $entranceCode = ""
    ) {
        parent::__construct($config, $entranceCode, $expected_return);
        if (isset($config->paymentBrandID)
            && $config->paymentBrandID !== ""
        ) {
            $this->brandID = $config->paymentBrandID;
        } else {
            $this->brandID = $config->brandID;
        }

        $this->transactionID = $transactionID;

        $this->context = new PaymentsContext();
    }

    public function TransactionType(): string
    {
        return $this->transaction_code;
    }

    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'PaymentStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );
    }
}
