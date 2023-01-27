<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\MandatesContext;
use Bluem\BluemPHP\Helpers\BluemConfiguration;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;

/**
 * TransactionRequest
 */
class EmandateBluemRequest extends BluemRequest implements BluemRequestInterface {
    public $typeIdentifier = "createTransaction";
    public $request_url_type = "mr";
    public $transaction_code = "TRX";
    protected $merchantID;
    protected $merchantSubID;
    private string $localInstrumentCode;
    private $merchantReturnURLBase;
    private $merchantReturnURL;
    private $sequenceType;
    private ?string $eMandateReason = null;
    private string $purchaseID;
    private string $automatically_redirect;
    private string $xmlInterfaceName = "EMandateInterface";

    /**
     * @param BluemConfiguration $config
     * @param $customer_id
     * @param $order_id
     * @param $mandateID
     * @param string $expected_return
     *
     * @throws Exception
     */
    public function __construct( BluemConfiguration $config, private $debtorReference, $order_id, $mandateID, string $expected_return = "none" ) {
        parent::__construct( $config, "", $expected_return );

        $this->merchantReturnURLBase = $config->merchantReturnURLBase;

        // $this->request_type = $request_type;
        // if($this->request_type==="simple" && $simple_redirect_url!=="") {
        // 	$this->merchantReturnURL = $simple_redirect_url."?mandateID={$this->mandateID}";
        // }

        $this->localInstrumentCode = $config->localInstrumentCode;
        // @todo create localInstrumentCode datatype with these options // CORE | B2B ,  conform gegeven standaard
        $this->mandateID = $mandateID;

        // https - unique return URL for customer
        $this->merchantReturnURL = "$this->merchantReturnURLBase?mandateID=$this->mandateID";
        $this->sequenceType = $config->sequenceType ?? "RCUR";
        // reason for the mandate; configurable per client
        $this->eMandateReason = $config->eMandateReason ?? "Incasso machtiging";

        // inkoop/contract/order/klantnummer
        /* PurchaseID is a mandatory field of the banks.
        It translates to the customer as 'purchase number' or 'order number' (depending on the bank).
        We do not present it on the checkout, because we see that many parties
        really do not know what to put there. We always advise customer number.
        And that is what many parties do. */
        if ( property_exists($config, 'purchaseIDPrefix') && $config->purchaseIDPrefix !== null && $config->purchaseIDPrefix !== "" ) {
            $purchaseIDPrefix = $config->purchaseIDPrefix . "-";
        } else {
            $purchaseIDPrefix = "";
        }
        $this->purchaseID = substr( "$purchaseIDPrefix$this->debtorReference-$order_id", 0, 34 );  // INKOOPNUMMER


        // @todo: move to mandate-specifics; as it is only necessary there
        $this->merchantID = $config->merchantID ?? "";

        // override with hardcoded merchantID when in test environment, according to documentation
        if ( $this->environment === BLUEM_ENVIRONMENT_TESTING ) {
            $this->merchantID = "0020000387";
        }

        $this->merchantSubID = $config->merchantSubID ?? "0";


        $this->automatically_redirect = "1";


        $this->context = new MandatesContext( $config->localInstrumentCode );
    }

    public function XmlString(): string {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'EMandateTransactionRequest',
                '<MandateID>' . $this->mandateID . '</MandateID>
                <MerchantReturnURL automaticRedirect="' . $this->automatically_redirect . '">' . $this->merchantReturnURL . '</MerchantReturnURL>
                <SequenceType>' . $this->sequenceType . '</SequenceType>
                <EMandateReason>' . $this->eMandateReason . '</EMandateReason>
                <DebtorReference>' . $this->debtorReference . '</DebtorReference>
                <PurchaseID>' . $this->purchaseID . '</PurchaseID>' .
                $this->XmlWrapDebtorAdditionalData(),
                [
                    // 'entranceCode'=>$this->entranceCode,  always sent already
                    'requestType'         => "Issuing",
                    'localInstrumentCode' => $this->localInstrumentCode,
                    'merchantID'          => $this->merchantID,
                    'merchantSubID'       => $this->merchantSubID,
                ]
            )
        );
        /* OLD:

                $raw = '<?xml version="1.0"?>
        <EMandateInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        type="TransactionRequest"
        mode="direct"
        senderID="'.$this->senderID.'"
        version="1.0"
        createDateTime="'.$this->createDateTime.'"
        messageCount="1">
        <EMandateTransactionRequest entranceCode="'.$this->entranceCode.'"
        requestType="Issuing"
        localInstrumentCode="'.$this->localInstrumentCode.'"
        merchantID="'.$this->merchantID.'"
        merchantSubID="'.$this->merchantSubID.'"
        language="nl"
        sendOption="none">

        </EMandateTransactionRequest>
        </EMandateInterface>';
                return $raw;
                */
    }

    public function TransactionType(): string {
        return "TRX";
    }
    // @todo: deprecated, remove
}
