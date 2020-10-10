<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
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
    public $typeIdentifier = "requestStatus";
    public $transaction_code = "PSX";

    public function TransactionType(): String
    {
        return $this->transaction_code;
    }

    function __construct($config, $transactionID, $expected_return = "", $entranceCode = "")
    {
        parent::__construct($config,  $entranceCode, $expected_return);

        $this->transactionID = $transactionID;
    }

    public function XmlString(): String
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


class PaymentBluemRequest extends BluemRequest
{
    private $xmlInterfaceName = "EPaymentInterface";
    public $request_url_type = "pr";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "PTX";

    public function TransactionType(): String
    {
        return $this->transaction_code;
    }

    public function __construct(
        Object $config,
        $description,
        $debtorReference,
        $amount,
        $dueDateTime = null,
        $currency = null,
        $transactionID = null,
        $entranceCode,
        String $expected_return = "none"
    ) {
        parent::__construct($config, $entranceCode, $expected_return);

        $this->description = $description;

        //  Default Currency EUR
        if (is_null($currency)) {
            $this->currency = "EUR";
        } else {
            $this->currency = $currency;
            // @todo validate based on a list of accepted currencies
        }

        if (is_null($dueDateTime)) {
            $this->dueDateTime = Carbon::now()->addDays(1)->toDateTimeLocalString() . ".000Z";
        } else {
            $this->dueDateTime = Carbon::parse($dueDateTime)->toDateTimeLocalString() . ".000Z";
        }

        $this->debtorReference = $debtorReference;

        $this->amount = $this->parseAmount($amount);

        $this->transactionID = $transactionID;
        $this->debtorReturnURL = $config->merchantReturnURLBase . "?entranceCode={$this->entranceCode}&transactionID={$this->transactionID}";
        $this->debtorReturnURL = str_replace('&', '&amp;', $this->debtorReturnURL);
        // note! different variable name in config
        // added entranceCode as well, useful. Defined in generic bluem request class

        $this->paymentReference = "{$this->debtorReference}{$this->transactionID}";
    }

    /**
     * Parsing amount properly as a float, with decimals
     *
     * @param String $amount
     * @return Float
     */
    private function parseAmount(String $amount) : String
    {
        $amount = str_replace(',', '.', $amount);
        if (strpos($amount, '.') == false) {
            $amount .= '.00';
        }
        return $amount;
    }

    public function XmlString(): String
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
                    'documentType' => "PayRequest",
                    'sendOption' => "none",
                    'language' => "nl"
                ]
            )
        );

        // @todo make documentType, sendOption and language a setting here?
    }
}
