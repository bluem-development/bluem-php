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

    private $_inputIban;
    private $_inputName;
    private $_debtorReference;

    public $request_url_type = "icr";
    public $typeIdentifier = "createTransaction";

    public $transaction_code = "INX";

    public function TransactionType() : String
    {
        return "INX";
    }

    public function __construct(
        $config,
        $entranceCode,
        String $_inputIban,
        String $_inputName,
        String $_debtorReference = ""
    ) {
        parent::__construct($config, $entranceCode, "");

        $this->_inputIban = $_inputIban;
        $this->_inputName = $_inputName;
        $this->_debtorReference = $_debtorReference;

        $this->context = new IBANCheckContext();
    }

    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IBANCheckTransactionRequest',
                PHP_EOL.'<IBAN>'.$this->_inputIban.'</IBAN>'.PHP_EOL.
                '<AssumedName>'.$this->_inputName.'</AssumedName>'.PHP_EOL.
                '<DebtorReference>'.$this->_debtorReference.'</DebtorReference>'.PHP_EOL,
                []
            )
        );
    }
}


// @this has to be extended
