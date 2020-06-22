<?php 
namespace bluem;

/**
 * Plugin Name: BlueM eMandate integration for WordPress
 * Version: 1.0.0
 * Plugin URI: https://github.com/DaanRijpkema/bluem-wordpress
 * Description: BlueM Wordpress
 * Author: Daan Rijpkema
 * Author URI: https://github.com/DaanRijpkema/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * 	EMandateResponse
 */
class EMandateResponse extends \SimpleXMLElement
{

	/**
	 * Return if the response is a successfull one, in boolean
	 */
	public function Status() : Bool
	{
		if(isset($this->EMandateErrorResponse)) 
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Return the error message, if there is one. Else return null
	 */
	public function Error()
	{
		if(isset($this->EMandateErrorResponse))
		{

			return $this->EMandateErrorResponse->Error;
		}
		return null;
	}

}

/**
 * EMandateErrorResponse
 */
class EMandateErrorResponse
{
	private $error;

	public function __construct(String $error) {
		$this->error = $error;	
	}

	public function SetErrorMessage(String $error)
	{
	}
	public function Status() : Bool
	{
		return false;
	}
	public function Error()
	{
		return $this->error;
	}
}