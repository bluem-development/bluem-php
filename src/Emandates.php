<?php 
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
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
    public $type_identifier = "requestStatus"; 
    public $transaction_code = "SRX";    
    
    	
	function __construct($config,$mandateID,$expected_return="",$entranceCode="")
	{
		parent::__construct($config,$expected_return,$entranceCode);
		$this->type_identifier = "requestStatus";
		
		$this->mandateID = $mandateID;
	}

	public function TransactionType()
	{
		return "SRX";
	}

	public function XmlString()
	{
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<EMandateInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="StatusRequest"
    mode="direct" senderID="'.$this->senderID.'" version="1.0" createDateTime="'.$this->createDateTime.'"
    messageCount="1">
    <EMandateStatusRequest entranceCode="'.$this->entranceCode.'">
        <MandateID>'.$this->mandateID.'</MandateID>
    </EMandateStatusRequest>
</EMandateInterface>';
	}
	
}


/**
 * TransactionRequest
 */
class EmandateBluemRequest extends BluemRequest
{
    public $type_identifier = "createTransaction";   
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
	
	function __construct($config, $customer_id, $order_id, $mandateID, String $expected_return="none",
	$request_type = "default",
	$simple_redirect_url="")
	{
		
		parent::__construct($config,"",$expected_return);
		
		$this->type_identifier = "createTransaction";
		
		$this->merchantReturnURLBase = $config->merchantReturnURLBase;
		
		$this->request_type = $request_type;	
		if($this->request_type==="simple" && $simple_redirect_url!=="") {
			$this->merchantReturnURL = $simple_redirect_url."?mandateID={$this->mandateID}"; 
		}

		$now = Carbon::now();
		
		$this->localInstrumentCode = $config->localInstrumentCode; // CORE | B2B ,  conform gegeven standaard
		
		$this->mandateID = $mandateID;

		// https uniek returnurl voor klant
		$this->merchantReturnURL = $this->merchantReturnURLBase."?mandateID={$this->mandateID}"; 
		$this->sequenceType = "RCUR"; // TODO: uit config halen
		
		// reden van de machtiging; configurabel per partij
		$this->eMandateReason = "Incasso abonnement"; // TODO: uit config halen
		
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
		$this->purchaseID = "{$purchaseIDPrefix}{$this->debtorReference}-{$order_id}";  // INKOOPNUMMER

		

		$this->automatically_redirect = "1";

	}
	public function XmlString()
	{
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
<MandateID>'.$this->mandateID.'</MandateID>
<MerchantReturnURL automaticRedirect="'.$this->automatically_redirect.'">'.$this->merchantReturnURL.'</MerchantReturnURL>
<SequenceType>'.$this->sequenceType.'</SequenceType>
<EMandateReason>'.$this->eMandateReason.'</EMandateReason>
<DebtorReference>'.$this->debtorReference.'</DebtorReference>
<PurchaseID>'.$this->purchaseID.'</PurchaseID>
</EMandateTransactionRequest>
</EMandateInterface>';
		return $raw;
	}

	public function TransactionType() : String
	{
        return "TRX";
	}
	

	
}
