<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IdentityContext;

class IdentityStatusBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $typeIdentifier = "requestStatus";
    public $transaction_code = "ISX";

    public function TransactionType(): string
    {
        return "ISX";
    }

    public function __construct($config, $entranceCode, $expectedReturn, $transactionID)
    {
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID when using IDIN
        if (isset($config->IDINBrandID) && $config->IDINBrandID !== "") {
            $this->brandID = $config->IDINBrandID;
        } else {
            $this->brandID = $config->brandID;
        }

        $this->transactionID = $transactionID;

        $this->context = new IdentityContext();
    }


    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'IdentityStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );
    }
}
