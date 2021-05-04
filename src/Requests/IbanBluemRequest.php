<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IBANCheckContext;

class IbanBluemRequest extends BluemRequest
{
    private $xmlInterfaceName = "IBANCheckInterface";

    private $_inputIban;
    private $_inputName;
    private $_debtorReference;

    public $request_url_type = "icr";
    public $typeIdentifier = "createTransaction";

    public $transaction_code = "INX";

    public function TransactionType(): string
    {
        return "INX";
    }

    public function __construct(
        $config,
        $entranceCode,
        string $_inputIban,
        string $_inputName,
        string $_debtorReference = ""
    )
    {
        parent::__construct($config, $entranceCode, "");

        $this->_inputIban = $_inputIban;
        $this->_inputName = $_inputName;
        $this->_debtorReference = $_debtorReference;

        $this->context = new IBANCheckContext();
    }

    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IBANCheckTransactionRequest',
                PHP_EOL .
                '<IBAN>' .
                $this->_inputIban .
                '</IBAN>' . PHP_EOL .
                '<AssumedName>' .
                $this->_inputName .
                '</AssumedName>' . PHP_EOL .
                '<DebtorReference>' .
                $this->_debtorReference .
                '</DebtorReference>' . PHP_EOL,
                []
            )
        );
    }
}
