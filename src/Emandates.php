<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Carbon\Carbon;

/**
 * 	EMandateStatusRequest
 */
class EmandateStatusBluemRequest extends BluemRequest
{
    public $typeIdentifier = "requestStatus";
    public $request_url_type = "mr";
    public $transaction_code = "SRX";


    public function __construct(
        $config, $mandateID, $entranceCode="", $expected_return=""
    ) {
        parent::__construct(
            $config, $entranceCode, $expected_return
        );

        $this->xmlInterfaceName = "EMandateInterface";
        $this->typeIdentifier = "requestStatus";

        $this->mandateID = $mandateID;

        $this->context = new MandatesContext(
            $config->localInstrumentCode
        );
    }

    public function TransactionType()
    {
        return "SRX";
    }

    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'EMandateStatusRequest',
                '<MandateID>'.$this->mandateID.'</MandateID>'
            )
        );

        /* // Reference
                return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <EMandateInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="StatusRequest"
            mode="direct" senderID="'.$this->senderID.'" version="1.0" createDateTime="'.$this->createDateTime.'"
            messageCount="1">
            <EMandateStatusRequest entranceCode="'.$this->entranceCode.'">
                <MandateID>'.$this->mandateID.'</MandateID>
            </EMandateStatusRequest>
        </EMandateInterface>';
        */
    }
}

/**
 * TransactionRequest
 */
class EmandateBluemRequest extends BluemRequest
{
    public $typeIdentifier = "createTransaction";
    public $request_url_type = "mr";
    public $transaction_code = "TRX";

    private $localInstrumentCode;
    private $merchantReturnURLBase;
    private $merchantReturnURL;
    private $sequenceType;
    private $eMandateReason;
    private $debtorReference;
    private $purchaseID;
    private $sendOption;


    protected $merchantID;
    protected $merchantSubID;

    public function __construct($config, $customer_id, $order_id, $mandateID, String $expected_return="none")
    {
        parent::__construct($config, "", $expected_return);

        $this->xmlInterfaceName = "EMandateInterface";
        // $this->request_url_type = "mr";
        $this->typeIdentifier = "createTransaction";


        $this->merchantReturnURLBase = $config->merchantReturnURLBase;

        // $this->request_type = $request_type;
        // if($this->request_type==="simple" && $simple_redirect_url!=="") {
        // 	$this->merchantReturnURL = $simple_redirect_url."?mandateID={$this->mandateID}";
        // }

        $now = Carbon::now()->timezone('Europe/Amsterdam');

        $this->localInstrumentCode = $config->localInstrumentCode; // CORE | B2B ,  conform gegeven standaard

        $this->mandateID = $mandateID;

        // https uniek returnurl voor klant
        $this->merchantReturnURL = $this->merchantReturnURLBase."?mandateID={$this->mandateID}";
        if (isset($config->sequenceType)) {
            $this->sequenceType = $config->sequenceType;
        } else {
            $this->sequenceType = "RCUR";
        }

        // reden van de machtiging; configurabel per partij
        if (isset($config->eMandateReason)) {
            $this->eMandateReason = $config->eMandateReason;
        } else {
            $this->eMandateReason = "Incasso machtiging";
        }
        // $this->eMandateReason = ; // TODO: uit config halen

        // Klantreferentie bijv naam of nummer
        $this->debtorReference = $customer_id;

        // inkoop/contract/order/klantnummer
        /* PurchaseID is verplichtveld van de banken.
        Dit vertalen het naar de klant als ‘inkoopnummer’ of ‘ordernummer’ (afh. Bank).
        Wij presenteren het niet op de checkout, omdat wij zien dat veel partijen
        echt niet weten wat ze er in moeten zetten. Wij adviseren dan altijd klantnummer.
        En dat doet dan ook veel partijen */
        if (isset($config->purchaseIDPrefix) && $config->purchaseIDPrefix!=="") {
            $purchaseIDPrefix = $config->purchaseIDPrefix."-";
        } else {
            $purchaseIDPrefix = "";
        }
        $this->purchaseID = substr("{$purchaseIDPrefix}{$this->debtorReference}-{$order_id}", 0, 34);  // INKOOPNUMMER


        // todo: move to mandate-specifics; as it is only necessary there
        if (isset($config->merchantID)) {
            $this->merchantID = $config->merchantID;
        } else {
            $this->merchantID = "";
        }

        // override with hardcoded merchantID when in test environment, according to documentation
        if ($this->environment === BLUEM_ENVIRONMENT_TESTING) {
            $this->merchantID = "0020000387";
        }

        if (isset($config->merchantSubID)) {
            $this->merchantSubID = $config->merchantSubID;
        } else {
            $this->merchantSubID = "0";
        }


        $this->automatically_redirect = "1";


        $this->context = new MandatesContext($config->localInstrumentCode);
    }
    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'EMandateTransactionRequest',
                '<MandateID>'.$this->mandateID.'</MandateID>
                <MerchantReturnURL automaticRedirect="'.$this->automatically_redirect.'">'.$this->merchantReturnURL.'</MerchantReturnURL>
                <SequenceType>'.$this->sequenceType.'</SequenceType>
                <EMandateReason>'.$this->eMandateReason.'</EMandateReason>
                <DebtorReference>'.$this->debtorReference.'</DebtorReference>
                <PurchaseID>'.$this->purchaseID.'</PurchaseID>'.
                $this->XmlWrapDebtorWallet().
                $this->XmlWrapDebtorAdditionalData(),
                [
                    // 'entranceCode'=>$this->entranceCode,  always sent already
                    'requestType'=>"Issuing",
                    'localInstrumentCode'=>$this->localInstrumentCode,
                    'merchantID'=>$this->merchantID,
                    'merchantSubID'=>$this->merchantSubID,
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

    public function TransactionType() : String
    {
        return "TRX";
    }
}
