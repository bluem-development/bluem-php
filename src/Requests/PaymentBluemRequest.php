<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;
use Carbon\Carbon;

class PaymentBluemRequest extends BluemRequest
{
    private $xmlInterfaceName = "EPaymentInterface";
    public $request_url_type = "pr";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "PTX";

    public function TransactionType(): string
    {
        return $this->transaction_code;
    }

    public function __construct(
        \stdclass $config,
        $description,
        $debtorReference,
        $amount,
        $dueDateTime = null,
        $currency = null,
        $transactionID = null,
        $entranceCode = "",
        string $expected_return = "none",
        $debtorReturnURL = ""
    ) {
        parent::__construct($config, $entranceCode, $expected_return);


        if (isset($config->paymentBrandID) && $config->paymentBrandID !== "") {
            $this->brandID = $config->paymentBrandID;
        } else {
            $this->brandID = $config->brandID;
        }

        $this->description = $this->_sanitizeDescription($description);

        //  Default Currency EUR
        if (is_null($currency)) {
            $this->currency = "EUR";
        } else {
            $this->currency = $currency;
            // @todo validate based on a list of accepted currencies
        }

        if (is_null($dueDateTime)) {
            $this->dueDateTime = Carbon::now()->addDays(1)->format(BLUEM_LOCAL_DATE_FORMAT) . ".000Z";
        } else {
            $this->dueDateTime = Carbon::parse($dueDateTime)->format(BLUEM_LOCAL_DATE_FORMAT) . ".000Z";
        }

        $this->debtorReference = $debtorReference;

        $this->amount = $this->parseAmount($amount);

        $this->transactionID = $transactionID;

        if (isset($debtorReturnURL) && $debtorReturnURL != "") {
            $this->debtorReturnURL = $debtorReturnURL;
        } else {
            $this->debtorReturnURL = $config->merchantReturnURLBase;
        }
        $this->debtorReturnURL .= "?entranceCode={$this->entranceCode}&transactionID={$this->transactionID}";

        $this->debtorReturnURL = str_replace('&', '&amp;', $this->debtorReturnURL);

        // note! different variable name in config
        // added entranceCode as well, useful. Defined in generic bluem request class

        $this->paymentReference = "{$this->debtorReference}{$this->transactionID}";

        $this->context = new PaymentsContext();
    }

    /**
     * Parsing amount properly as a float, with decimals
     *
     * @param String $amount
     *
     * @return Float
     */
    private function parseAmount(string $amount): string
    {
        $amount = str_replace(',', '.', $amount);
        if (strpos($amount, '.') == false) {
            $amount .= '.00';
        }

        return $amount;
    }

    public function XmlString(): string
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
                <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>' .
                $this->XmlWrapDebtorWallet() .
                $this->XmlWrapDebtorAdditionalData(),
                [
                    'documentType' => "PayRequest",
                    'sendOption'   => "none",
                    'language'     => "nl",
                ]
            )
        );

        // @todo make documentType, sendOption and language a setting here?
    }
}
