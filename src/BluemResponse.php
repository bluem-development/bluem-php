<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Exception;

/**
 * 	BluemResponse
 */
class BluemResponse extends \SimpleXMLElement
{


	/**
	 * Response Primary Key used to access the XML structure based on the specific type of response
	 *
	 * @var String
	 */
	static $response_primary_key;

	/** Transaction type used to differentiate the specific type of response
	 *
	 * @var String
	 */

	static $transaction_type;

	/** Error response type used to differentiate the specific type of response
	 *
	 * @var String
	 */

	static $error_response_type;

	/**
	 * Return if the response is a successfull one, in boolean
	 *
	 * @return Bool
	 */
	public function Status(): Bool
	{
		// $key = 
		if (isset($this->{static::$error_response_type})) {
			return false;
		}
		return true;
	}

	public function ReceivedResponse()
	{
		return $this->Status();
	}

	/**
	 * Return the error message, if there is one. Else return null
	 *
	 */
	public function Error()
	{
		if (isset($this->EMandateErrorResponse)) {

			return $this->EMandateErrorResponse->Error;
		}
		return null;
	}

	/**
	 * Retrieve the generated Entrancecode enclosed in this response
	 *
	 * @return String
	 */
	public function GetEntranceCode(): String
	{
		$attrs = $this->{$this->getParentXmlElement()}->attributes();

		if (!isset($attrs['entranceCode'])) {
			return null;
			// throw new \Exception("An error occured in reading the transaction response: no entrance code found.");
		}
		$entranceCode = $attrs['entranceCode'] . "";
		return $entranceCode;
	}

	protected function getParentXmlElement()
	{
		// overriden in children
		return; //static::$response_primary_key . "Response";
	}

	protected function getChildXmlElement()
	{
		return static::$response_primary_key;
	}


	// @todo only add this in transaction-based responses
	public function GetTransactionURL()
	{
	}
	public function GetTransactionID()
	{
	}
	public function GetStatusCode()
	{
	}
	public function GetIdentityReport()
	{
	}
}

/**
 * EMandateErrorResponse
 */
class ErrorBluemResponse
{
	private $error;

	public function __construct(String $error)
	{
		$this->error = $error;
	}

	public function Status(): Bool
	{
		return false;
	}

	public function ReceivedResponse()
	{
		return false;
	}
	public function Error()
	{
		return $this->error;
	}
}



class TransactionBluemResponse extends BluemResponse
{
	public function GetTransactionURL()
	{
		return (isset($this->{$this->getParentXmlElement()}->TransactionURL)) ? $this->{$this->getParentXmlElement()}->TransactionURL . "" : null;
	}

	public function GetTransactionID()
	{
		return (isset($this->{$this->getParentXmlElement()}->TransactionID)) ? $this->{$this->getParentXmlElement()}->TransactionID . "" : null;
	}

	protected function getParentXmlElement()
	{
		return static::$response_primary_key . "Response";
	}
}


class StatusBluemResponse extends BluemResponse
{

	public function GetStatusCode()
	{
		$parent_key = $this->getParentXmlElement(); //"{$this->response_primary_key}Update";
		echo $parent_key;
		$child_key = $this->getChildXmlElement(); //"{$this->response_primary_key}";
		echo $child_key;
		if (isset($this->{$parent_key}->{$child_key}->Status)) {
			return $this->{$parent_key}->{$child_key}->Status . "";
		}
		return null;
	}

	protected function getParentXmlElement()
	{
		return static::$response_primary_key . "Update";
	}
}


class MandateTransactionBluemResponse extends TransactionBluemResponse
{
	static $transaction_type = "EMandate";
	static $response_primary_key = "EMandate" . "Transaction";
	static $error_response_type = "EMandate" . "ErrorResponse";

	// @todo Function to retrieve Mandate ID?

}

class MandateStatusBluemResponse extends StatusBluemResponse
{
	static $transaction_type = "EMandate";
	static $response_primary_key = "EMandate" . "Status";
	static $error_response_type = "EMandate" . "ErrorResponse";
}

class PaymentTransactionBluemResponse extends TransactionBluemResponse
{

	static $transaction_type = "Payment";
	static $response_primary_key = "Payment" . "Transaction";
	static $error_response_type = "Payment" . "ErrorResponse";
}

class PaymentStatusBluemResponse extends StatusBluemResponse
{
	static $transaction_type = "Payment";
	static $response_primary_key = "Payment" . "Status";
	static $error_response_type = "Payment" . "ErrorResponse";
}


class IdentityStatusBluemResponse extends StatusBluemResponse
{
	static $transaction_type = "Identity";
	static $response_primary_key = "Identity" . "Status";
	static $error_response_type = "Identity" . "ErrorResponse";


	public function GetStatusCode()
	{
		if (isset($this->{$this->getParentXmlElement()}->Status)) {
			return $this->{$this->getParentXmlElement()}->Status . "";
		}
		return null;
	}


	public function GetIdentityReport()
	{
		if (isset($this->{$this->getParentXmlElement()}->IdentityReport)) {
			return $this->{$this->getParentXmlElement()}->IdentityReport;
		}
		return null;
	}

	public function GetAuthenticationAuthorityID()
	{
		if (isset($this->{$this->getParentXmlElement()}->AuthenticationAuthorityID)) {
			return $this->{$this->getParentXmlElement()}->AuthenticationAuthorityID . "";
		}
		return null;
	}
}
class IdentityTransactionBluemResponse extends TransactionBluemResponse
{
	static $transaction_type = "Identity";
	static $response_primary_key = "Identity" . "Transaction";
	static $error_response_type = "Identity" . "ErrorResponse";
}


// 	@todo add IBAN check response type
	// static $error_response_type = "IBANCheckErrorResponse";
