<?php 
namespace Bluem\BluemPHP;

// if (!defined('ABSPATH')) {
//     exit;
// }

use Carbon\Carbon;

/**
 * 	EMandateRequest
 */
class EMandateRequest
{
	public $type_identifier;

	public $entranceCode;
	public $mandateID;

	protected $senderID;
	protected $merchantID;
	protected $merchantSubID;
	protected $createDateTime;


	function __construct($config,$entranceCode="",$expected_return="")
	{
		$this->environment = $config->environment;
		
		$this->senderID = $config->senderID;
		
		$this->merchantID = $config->merchantID;

		// override with hardcoded merchantID when in test environment, according to documentation
		if($this->environment === BLUEM_ENVIRONMENT_TESTING) {
			$this->merchantID = "0020000387"; 
		}

		$this->merchantSubID = $config->merchantSubID;

		$this->accessToken = $config->accessToken;

		$this->createDateTime = Carbon::now()->toDateTimeLocalString().".000Z";

		// uniek in de tijd voor emandate; string; niet zichtbaar voor klant; 
		// uniek kenmerk van incassant voor deze transactie
		// structuur: prefix voor testing + klantnummer + huidige timestamp tot op de seconde
		if($entranceCode==="") {

			$this->entranceCode = $this->entranceCode($expected_return);
		} else {
			$this->entranceCode = $entranceCode;
		}
	}
	
	public function XmlString()
	{
		return "";
	}
	public function Xml()
	{
		return new SimpleXMLElement($this->XmlString());
	}

	/**
	 * Prints a request, for testing purposes
	 *
	 * @param      EMandateTransactionRequest  $r      The TransactionRequest Object
	 */
	public function Print() 
	{
		header('Content-Type: text/xml; charset=UTF-8');
		print($this->XmlString());
	}

	/**
	 * Gets the http request url.
	 *
	 * @param      string     $call   The call identifier as a string
	 *
	 * @throws     Exception  (description)
	 *
	 * @return     string     The http request url.
	 */
	public function HttpRequestURL() : String
	{
		switch ($this->environment) {
			case BLUEM_ENVIRONMENT_TESTING:
			{
				$request_url = "https://test.viamijnbank.net/mr/";
				break;
			}
			case BLUEM_ENVIRONMENT_ACCEPTANCE:
			{
				$request_url = "https://acc.viamijnbank.net/mr/";
				break;
			}
			case BLUEM_ENVIRONMENT_PRODUCTION:
			{
				$request_url = "https://viamijnbank.net/mr/";
				break;
			}
			default:
				throw new Exception("Invalid environment setting", 1);
				break;
		}

		switch ($this->type_identifier) {
			case 'createTransaction':
			{
				$request_url .= "createTransactionWithToken";
				break;
			}
			case 'requestStatus':
			{
				$request_url .= "requestTransactionStatusWithToken";
				break;
			}
			default:
				throw new Exception("Invalid call called for", 1);
				break;
		}
		$request_url.= "?token={$this->accessToken}";
		return $request_url;
	}

	public function TransactionType() : String
	{
		switch ($this->type_identifier) {
			case 'createTransaction':  	// EMandate createTransaction (TRX) 
			{ 
				return "TRX";
			}
			case 'requestStatus':  		// EMandate requestStatus (SRX) 
			{ 
				return "SRX";
			}
			case 'createTransaction':  	// IDentity createTransaction (ITX) 
			{ 
				return "ITX";
			}
			case 'requestStatus':  		// IDentity requestStatus (ISX) 
			{ 
				return "ISX";
			}
			case 'createTransaction': 	// IBANCheck createTransaction (INX)
			{ 
				return "INX";
			}
			default:
			{
				throw new Exception("Invalid call called for",1);
				break;
			}
		}
	}
	// test entranceCode substrings voor bepaalde types return responses
	private function entranceCode($expected_return,$override="")
	{
		$entranceCode = "";
		// only allow this in testing mode
		if($this->environment === BLUEM_ENVIRONMENT_TESTING) {
			switch ($expected_return) {
				case 'none':
				{
					$entranceCode = "";
				break;
				}
				case 'success':
				{
					$entranceCode = "HIO100OIH";
				break;
				}
				case 'cancelled':
				{
					$entranceCode = "HIO200OIH";
				break;
				}
				case 'expired':
				{
					$entranceCode = "HIO300OIH";
				break;
				}
				case 'failure':
				{
					$entranceCode = "HIO500OIH";
				break;
				}
				case 'open':
				{
					$entranceCode = "HIO400OIH";
				break;
				}
				case 'pending':
				{
					$entranceCode = "HIO600OIH";
				break;
				}
				default: {
					$entranceCode = "";
				break;
				}
			}
		}
		$entranceCode .= Carbon::now()->format('YmdHis').'000';
		return $entranceCode;
	}

	
}


/**
 * 	EMandateStatusRequest
 */
class EMandateStatusRequest extends EMandateRequest
{
	
	function __construct($config,$mandateID,$expected_return="",$entranceCode="")
	{
		parent::__construct($config,$expected_return,$entranceCode);
		$this->type_identifier = "requestStatus";
		
		$this->mandateID = $mandateID;
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
class EMandateTransactionRequest extends EMandateRequest
{
	
	
	private $localInstrumentCode;
	private $merchantReturnURLBase;
	private $merchantReturnURL;
	private $sequenceType;
	private $eMandateReason;
	private $debtorReference;
	private $purchaseID;
	private $sendOption;
	
	function __construct($config, $customer_id, $order_id, $mandateID, String $expected_return="none",
	$transaction_type = "default",
	$simple_redirect_url="")
	{
		
		parent::__construct($config,"",$expected_return);
		
		$this->type_identifier = "createTransaction";
		
		$this->merchantReturnURLBase = $config->merchantReturnURLBase;
		
		$this->transaction_type = $transaction_type;	
		if($this->transaction_type==="simple" && $simple_redirect_url!=="") {
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
	

	
}
