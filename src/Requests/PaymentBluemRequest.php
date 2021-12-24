<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\PaymentsContext;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Carbon\Carbon;
use Exception;
use stdclass;

class PaymentBluemRequest extends BluemRequest implements BluemRequestInterface
{
    private $xmlInterfaceName = "EPaymentInterface";
    public $request_url_type = "pr";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "PTX";
    /**
     * @var string
     */
    private $description;
    /**
     * @var mixed|string
     */
    private $currency;
    /**
     * @var string
     */
    private $dueDateTime;
    private $debtorReference;
    /**
     * @var float
     */
    private $amount;
    /**
     * @var array|string|string[]
     */
    private $debtorReturnURL;
    /**
     * @var string
     */
    private $paymentReference;
// @todo: deprecated, remove
    public function TransactionType(): string
    {
        return $this->transaction_code;
    }

    /**
     * @throws Exception
     */
    public function __construct(
        BluemConfiguration $config,
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
            $config->setBrandId($config->paymentBrandID);
        } else {
            $config->setBrandId($config->brandID);
        }

        $this->description = $this->_sanitizeDescription($description);

        //  Default Currency EUR
        $this->currency = $this->validateCurrency($currency);

        

        if (is_null($dueDateTime)) {
            $this->dueDateTime = Carbon::now()->addDay()->format(BLUEM_LOCAL_DATE_FORMAT) . ".000Z";
        } else {
            $this->dueDateTime = Carbon::parse($dueDateTime)->format(BLUEM_LOCAL_DATE_FORMAT) . ".000Z";
        }

        //  @todo: validate DebtorReference : [0-9a-zA-Z]{1,35}
        $sanitizedDebtorReferenceParts = [];
        $sanitizedDebtorReferenceCount = preg_match_all(
            "/[0-9a-zA-Z]{1,35}/i",
            $debtorReference,
            $sanitizedDebtorReferenceParts
        );
        if($sanitizedDebtorReferenceCount!==false && $sanitizedDebtorReferenceCount>0) {
            $debtorReference = implode(
                "",
                $sanitizedDebtorReferenceParts[0]
            );
        }
        $this->debtorReference = $debtorReference;
        

        $this->amount = floatval($amount);
        
        $this->transactionID = $transactionID;

        if (isset($debtorReturnURL) && $debtorReturnURL != "") {
            $this->debtorReturnURL = $debtorReturnURL;
        } else {
            $this->debtorReturnURL = $config->merchantReturnURLBase;
        }
        $this->debtorReturnURL .= "?entranceCode=$this->entranceCode&transactionID=$this->transactionID";

        $this->debtorReturnURL = str_replace('&', '&amp;', $this->debtorReturnURL);

        // note! different variable name in config
        // added entranceCode as well, useful. Defined in generic bluem request class

        $this->paymentReference = "$this->debtorReference$this->transactionID";

        $this->context = new PaymentsContext();
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
                <Amount>' . number_format($this->amount, 2, '.', '') . '</Amount>
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

    /**
     * validate based on a list of accepted currencies
     *
     * @param $currency
     * @return string
     * @throws Exception
     */
    private function validateCurrency($currency): string
    {
        $availableCurrencies = ["EUR"]; // @todo: add list of currencies based on 
        if(!in_array($currency,$availableCurrencies)) {
            throw new Exception("Currency not recognized, 
                    should be one of the following available currencies: ".
                implode( ",",$availableCurrencies)
            );
        }
        return $currency;
    }
}
