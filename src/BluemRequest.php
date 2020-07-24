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
 * 	BluemRequest
 */
class BluemRequest
{
	public $type_identifier;
	public $request_url_type;

	public $entranceCode;
	public $mandateID;

	protected $senderID;

	protected $createDateTime;


	function __construct($config, $entranceCode = "", $expected_return = "")
	{
		$this->environment = $config->environment;

		$this->senderID = $config->senderID;
		$this->brandID = $config->brandID;

		$this->accessToken = $config->accessToken;

		$this->createDateTime = Carbon::now()->toDateTimeLocalString() . ".000Z";

		// uniek in de tijd voor emandate; string; niet zichtbaar voor klant; 
		// uniek kenmerk van incassant voor deze transactie
		// structuur: prefix voor testing + klantnummer + huidige timestamp tot op de seconde
		if ($entranceCode === "") 
		{
			$this->entranceCode = $this->entranceCode($expected_return);
		} else {
			$this->entranceCode = $entranceCode;
		}
	}


	protected function XmlRequestInterfaceWrap($element_name,$type="TransactionRequest",$rest) {

		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><'.$element_name.'
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        type="'.$type.'" 
        mode="direct" 
        senderID="'.$this->senderID.'" 
        version="1.0" 
        createDateTime="'.$this->createDateTime.'" 
        messageCount="1"
          >'.$rest.'</'.$element_name.'>';
	}

	protected function XmlRequestObjectWrap($element_name,$rest,$extra_attrs = [])
	{
		$res = '<'.$element_name.'
           entranceCode="'.$this->entranceCode.'" ';
		foreach ($extra_attrs as $key => $value) {
			$res .= $key.'="'.$value.'"'.PHP_EOL;
		}

		$res.='>'.$rest.'</'.$element_name.'>';
		return $res;
	}
	
	public function XmlString()
	{
		return "";
	}
	public function Xml()
	{
		return new \SimpleXMLElement($this->XmlString());
	}

	/**
	 * Prints a request, for testing purposes
	 *
	 * @param      BluemRequest  $r      The Request Object
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
	public function HttpRequestURL(): String
	{
		$request_url = "https://";
		switch ($this->environment) {
			case BLUEM_ENVIRONMENT_ACCEPTANCE: {
					$request_url .= "acc.";
					break;
				}
			case BLUEM_ENVIRONMENT_PRODUCTION: {
					$request_url .= "";
					break;
				}
			case BLUEM_ENVIRONMENT_TESTING:
			default: {
					$request_url .= "test.";
					break;
				}
		}
		$request_url .= "viamijnbank.net/{$this->request_url_type}/";

		switch ($this->type_identifier) {
			case 'createTransaction': {
					$request_url .= "createTransactionWithToken";
					break;
				}
			case 'requestStatus': {
					$request_url .= "requestTransactionStatusWithToken";
					break;
				}
			default:
				throw new \Exception("Invalid transactiontype called for", 1);
				break;
		}
		$request_url .= "?token={$this->accessToken}";
		return $request_url;
	}

	// test entranceCode substrings voor bepaalde types return responses
	private function entranceCode($expected_return, $override = "")
	{
		$entranceCode = "";
		// only allow this in testing mode
		if ($this->environment === BLUEM_ENVIRONMENT_TESTING) {
			switch ($expected_return) {
				case 'none': {
						$entranceCode = "";
						break;
					}
				case 'success': {
						$entranceCode = "HIO100OIH";
						break;
					}
				case 'cancelled': {
						$entranceCode = "HIO200OIH";
						break;
					}
				case 'expired': {
						$entranceCode = "HIO300OIH";
						break;
					}
				case 'failure': {
						$entranceCode = "HIO500OIH";
						break;
					}
				case 'open': {
						$entranceCode = "HIO400OIH";
						break;
					}
				case 'pending': {
						$entranceCode = "HIO600OIH";
						break;
					}
				default: {
						$entranceCode = "";
						break;
					}
			}
		}
		$entranceCode .= Carbon::now()->format('YmdHis') . '000';
		return $entranceCode;
	}
}


// class BluemStatusRequest extends BluemRequest {
// 	// for now identical to a normal request.
// }