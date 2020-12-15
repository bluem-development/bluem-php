<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;

use Carbon\Carbon as Carbon;
use Exception;

/**
 * 	BluemRequest
 */
class BluemRequest
{
    public $typeIdentifier;
    public $request_url_type;

    public $entranceCode;
    public $mandateID;

    protected $senderID;

    protected $createDateTime;

    public $debtorWallet = null;

    public $context;

    /**
     * Initialization of any request
     *
     * @param Array $config
     * @param String $entranceCode
     * @param String $expectedReturn
     */
    public function __construct(\Stdclass $config, String $entranceCode = "", String $expectedReturn = "")
    {
        $this->environment = $config->environment;

        $this->senderID = $config->senderID;
        $this->brandID = $config->brandID;

        $this->accessToken = $config->accessToken;

        $this->createDateTime = Carbon::now()->timezone('Europe/Amsterdam')->toDateTimeLocalString() . ".000Z";

        /**
         *  unique identifier of payee for this transaction
         *  which is unique in time for any request; which is string; which should not be visible for customer;
         *  structure: prefix for testing + customer number + current timestamp up to the second
        */
        if ($entranceCode === "") {  // if not given, create it
            $this->entranceCode = $this->entranceCode($expectedReturn);
        } else {
            $this->entranceCode = $entranceCode;
        }
    }

    public function getContext()
    {
        return $this->context;
    }


    /**
     * Construct the XML request string parent object for any request
     *
     * @param String $element_name Typically contains the interface of the current request context
     * @param String $type Type of request (transaction creation or status)
     * @param String $rest Remainder of XML element, as a string, used to chain this function
     * @return String Constructed XML as string
     */
    protected function XmlRequestInterfaceWrap(String $element_name, String $type="TransactionRequest", String $rest) : String
    {
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

    /**
     * Construct the XML request string objects
     *
     * @param String $element_name Typically contains the specific object of the current request context
     * @param String $rest Remainder of XML element, as a string, used to chain this function
     * @param Array $extra_attrs Any arbitrary other key-value pairs to be added as XML element attributes
     * @return String Constructed XML as string
     */
    protected function XmlRequestObjectWrap(String $element_name, String $rest, array $extra_attrs = []) : String
    {
        $res = "<{$element_name}
           entranceCode=\"{$this->entranceCode}\" ";
        foreach ($extra_attrs as $key => $value) {
            $res .= "{$key}=\"{$value}\" ".PHP_EOL;
        }
        $res.='>'.$rest.'</'.$element_name.'>';
        return $res;
    }

    /**
     * Returning the current XML string; as this is an abstract request, it will be overridden by classes that implement this.
     *
     * @return String
     */
    public function XmlString() : String
    {
        return "";
    }

    /**
     * Retrieve the final XML object based on the constructed XML String
     *
     * @return SimpleXMLElement final XML object
     */
    public function Xml() : \SimpleXMLElement
    {
        return new \SimpleXMLElement($this->XmlString());
    }

    /**
     * Prints a request as XML object with corresponding headers, in your browser,
     * mostly for testing purposes
     *
     */
    public function Print()
    {
        header('Content-Type: text/xml; charset=UTF-8');
        print($this->XmlString());
    }

    /**
     * Retrieves the http request url.
     *
     * @throws     Exception  (invalid transaction type called for, if not create transaction or status request)
     *
     * @retum string The http request url.
     */
    public function HttpRequestURL(): String
    {
        $request_url = "https://";

        switch ($this->environment) {
        case BLUEM_ENVIRONMENT_PRODUCTION: {
                $request_url .= "";
                break;
            }
        case BLUEM_ENVIRONMENT_ACCEPTANCE: {
                $request_url .= "acc.";
                break;
            }
        case BLUEM_ENVIRONMENT_TESTING:
        default: {
                $request_url .= "test.";
                break;
            }
        }
        $request_url .= "viamijnbank.net/{$this->request_url_type}/";

        switch ($this->typeIdentifier) {
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

    /**
     * Generate an entranceCode, including test entranceCode substrings for certain types of return responses
     *
     * @param string $expectedReturn a possible expected return value (none,success,cancelled,expired,failure,open,pending) or empty string
     * @param string $entranceCode a set entrance code, otherwise it gets generated based on dateTime string in "YmdHisv" standardized format, in Europe/Amsterdam timezone
     * @return void
     */
    private function entranceCode(String $expectedReturn, String $entranceCode = "")
    {

        // create a default entrancecode if necessary
        if ($entranceCode =="") {
            $entranceCode = Carbon::now()
                ->timezone('Europe/Amsterdam')
                ->format("YmdHisv");
        }

        $prefix = "";

        if ($this->environment === BLUEM_ENVIRONMENT_TESTING) {
            switch ($expectedReturn) {
            case 'none': {
                    $prefix = "";
                    break;
                }
            case 'success': {
                    $prefix = "HIO100OIH";
                    break;
                }
            case 'cancelled': {
                    $prefix = "HIO200OIH";
                    break;
                }
            case 'expired': {
                    $prefix = "HIO300OIH";
                    break;
                }
            case 'failure': {
                    $prefix = "HIO500OIH";
                    break;
                }
            case 'open': {
                    $prefix = "HIO400OIH";
                    break;
                }
            case 'pending': {
                    $prefix = "HIO600OIH";
                    break;
                }
            default: {
                    $prefix = "";
                    break;
                }
            }
        }

        $entranceCode = $prefix . $entranceCode;
        return $entranceCode;
    }
    /**
     * Retrieve array of objects with IssuerID and IssuerName of banks from the context
     *
     * @return array
     */
    public function retrieveBICObjects()
    {
        return $this->context->BICs();
    }

    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @return array
     */
    public function retrieveBICCodes()
    {
        return $this->context->getBICCodes();
    }
    /**
     * Package a certain BIC code to be sent with the response. It has to be a BIC valid for this context.
     *
     * @param [type] $BIC
     * @return void
     */
    public function selectDebtorWallet($BIC)
    {
        $possibleBICs = $this->context->getBICCodes();

        if (!in_array($BIC, $possibleBICs)) {
            throw new Exception("Invalid BIC code given, should be a valid BIC of a supported bank.");
        }
        $this->debtorWallet = $BIC;
    }

    /**
     * Create the XML element necessary to be added to the request XML string.
     *
     * @return string
     */
    public function XmlWrapDebtorWallet()
    {
        if (is_null($this->debtorWallet)) {
            return "";
        }

        if ($this->debtorWallet=="") {
            return "";
        }

        if (!isset($this->context->debtorWalletElementName) || $this->context->debtorWalletElementName=="") {
            return '';
        }

        $res = "<DebtorWallet>";
        $res .= "<{$this->context->debtorWalletElementName}>";
        $res .= "<BIC>".$this->debtorWallet."</BIC>";
        $res .= "</{$this->context->debtorWalletElementName}>";
        $res .= "</DebtorWallet>";
        return $res;
    }
}
