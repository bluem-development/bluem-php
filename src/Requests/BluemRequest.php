<?php

/*
 * (c) 2021 - Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Helpers\BluemConfiguration;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Carbon\Carbon as Carbon;
use Exception;
use SimpleXMLElement;

/**
 * BluemRequest general class
 */
class BluemRequest implements BluemRequestInterface
{
    /**
     * @var 
     */
    public $transaction_code;
    // @todo make an enum or a datatype?

    /**
     * @var 
     */
    public $typeIdentifier;
    /**
     * @var 
     */
    public $request_url_type;

    /**
     * @var string 
     */
    public $entranceCode;
    /**
     * @var 
     */
    public $mandateID;

    /**
     * @var 
     */
    public $debtorWallet = null;


    /**
     * @var
     */
    protected $senderID;
    /**
     * @var string
     */
    protected $createDateTime;

    /**
     * @var array
     */
    private $_debtorAdditionalData = [];
    /**
     * @var string[]
     */
    private $_possibleDebtorAdditionalDataKeys = [
        "EmailAddress",
        "MobilePhoneNumber",
        "CustomerProvidedDebtorIBAN",
        "CustomerNumber",
        "CustomerName",
        "AttentionOf",
        "Salutation",
        "CustomerAddressLine1",
        "CustomerAddressLine2",
        "DebtorBankID",
        "DynamicData",
    ];

    /**
     * @var
     */
    protected $transactionID;

    /**
     * @var
     */
    public $context;

    /**
     * @var string
     */
    protected $environment;


    /**
     * @var string
     */
    private $accessToken;

    /**
     * BluemRequest constructor.
     *
     * @param BluemConfiguration $config
     * @param string $entranceCode
     * @param string $expectedReturn
     * @throws Exception
     */
    public function __construct(
        BluemConfiguration $config,
        string $entranceCode = "",
        string $expectedReturn = ""
    )
    {
        $possibleTypeIdentifiers = ['createTransaction', 'requestStatus'];
        if (!in_array($this->typeIdentifier, $possibleTypeIdentifiers)) {
            throw new Exception("Invalid transaction type called for", 1);
        }
        // @todo: move to request validation class?
        
        $this->environment = $config->environment;

        $this->senderID = $config->senderID;
        $this->accessToken = $config->accessToken;
        // @todo just use the config directly instead of copying all configuration elements
        
        
        $this->createDateTime = Carbon::now()->timezone('Europe/Amsterdam')->format(BLUEM_LOCAL_DATE_FORMAT) . ".000Z";

        /**
         *  unique identifier of payee for this transaction
         *  which is unique in time for any request; which is string; which should not be visible for customer;
         *  structure: prefix for testing + customer number + current timestamp up to the second
         */
        // @todo Validate input entrance code if not empty string, based on XSD

        if ($entranceCode === "") {  // if not given, create it
            $this->entranceCode = $this->entranceCode($expectedReturn);
        } else {
            $this->entranceCode = $entranceCode;
        }
    }
    
    // @todo remove this?

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }


    /**
     * Construct the XML request string parent object for any request
     *
     * @param String $element_name Typically contains the interface of the current request context
     * @param String $type         Type of request (transaction creation or status)
     * @param String $rest         Remainder of XML element, as a string, used to chain this function
     *
     * @return String Constructed XML as string
     */
    protected function XmlRequestInterfaceWrap(
        string $element_name,
        string $type = "TransactionRequest",
        string $rest = ""
    ): string {
        // @Todo validate element name using a specific BluemXMLElement class
        // @todo validate type to be of specific options in BluemXMLElementType class
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><' . $element_name . '
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        type="' . $type . '"
        mode="direct"
        senderID="' . $this->senderID . '"
        version="1.0"
        createDateTime="' . $this->createDateTime . '"
        messageCount="1"
          >' . $rest . '</' . $element_name . '>';
    }

    /**
     * Construct the XML request string objects
     *
     * @param String $element_name Typically contains the specific object of the current request context
     * @param String $rest Remainder of XML element, as a string, used to chain this function
     * @param array $extra_attrs Any arbitrary other key-value pairs to be added as XML element attributes
     *
     * @return String Constructed XML as string
     */
    protected function XmlRequestObjectWrap(string $element_name, string $rest, array $extra_attrs = []): string
    {
        $res = "<$element_name
           entranceCode=\"$this->entranceCode\" ";
        foreach ($extra_attrs as $key => $value) {
            $res .= "$key=\"$value\" " . PHP_EOL;
        }
        $res .= '>' . $rest . '</' . $element_name . '>';

        return $res;
    }

    /**
     * Returning the current XML string; as this is an abstract request, it will be overridden by classes that
     * implement this.
     *
     * @return String
     */
    public function XmlString(): string
    {
        return "";
    }

    /**
     * Retrieve the final XML object based on the constructed XML String
     *
     * @return SimpleXMLElement final XML object
     * @throws Exception
     */
    public function Xml(): SimpleXMLElement
    {
        return new SimpleXMLElement($this->XmlString());
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
     * Crafts the relevant HTTP request url.
     *
     * @retum string The http request url.
     */
    public function HttpRequestURL(): string
    {
        $request_url = "https://";

        switch ($this->environment) {
            case BLUEM_ENVIRONMENT_PRODUCTION:
            {
                $request_url .= "";
                break;
            }
            case BLUEM_ENVIRONMENT_ACCEPTANCE:
            {
                $request_url .= "acc.";
                break;
            }
            case BLUEM_ENVIRONMENT_TESTING:
            default:
            {
                $request_url .= "test.";
                break;
            }
        }
        $request_url .= "viamijnbank.net/$this->request_url_type/";

        switch ($this->typeIdentifier) {
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
        }
        $request_url .= "?token=$this->accessToken";

        return $request_url;
    }

    /**
     * Generate an entranceCode, including test entranceCode substrings for certain types of return responses
     *
     * @param string $expectedReturn a possible expected return value
     *                               (none,success,cancelled,expired,failure,open,pending) or empty string
     *                               "YmdHisv" standardized format, in Europe/Amsterdam timezone
     *
     * @return string
     */
    private function entranceCode(string $expectedReturn = 'none'): string
    {
        $entranceCode = Carbon::now()
            ->timezone('Europe/Amsterdam')
            ->format("YmdHisv");

        $prefix = "";
        if ($this->environment === BLUEM_ENVIRONMENT_TESTING) {
            switch ($expectedReturn) {
                case 'success':
                {
                    $prefix = "HIO100OIH";
                    break;
                }
                case 'cancelled':
                {
                    $prefix = "HIO200OIH";
                    break;
                }
                case 'expired':
                {
                    $prefix = "HIO300OIH";
                    break;
                }
                case 'failure':
                {
                    $prefix = "HIO500OIH";
                    break;
                }
                case 'open':
                {
                    $prefix = "HIO400OIH";
                    break;
                }
                case 'pending':
                {
                    $prefix = "HIO600OIH";
                    break;
                }
                case '':
                case 'none':
                default:
                {
                    break;
                }
            }
        }

        return $prefix . $entranceCode;
    }

    /**
     * Retrieve array of objects with IssuerID and IssuerName of banks from the context
     *
     * @return array
     */
    public function retrieveBICObjects(): array
    {
        return $this->context->BICs();
    }

    /**
     * Retrieve array of BIC codes (IssuerIDs) of banks from context
     *
     * @return array
     */
    public function retrieveBICCodes(): array
    {
        return $this->context->getBICCodes();
    }

    /**
     * Package a certain BIC code to be sent with the response. It has to be a BIC valid for this context.
     *
     * @param [type] $BIC
     *
     * @return void
     * @throws Exception
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
    public function XmlWrapDebtorWallet(): string
    {
        if (is_null($this->debtorWallet)) {
            return "";
        }

        if ($this->debtorWallet == "") {
            return "";
        }

        if (!isset($this->context->debtorWalletElementName) || $this->context->debtorWalletElementName == "") {
            return '';
        }

        $res = PHP_EOL . "<DebtorWallet>" . PHP_EOL;
        $res .= "<{$this->context->debtorWalletElementName}>";
        $res .= "<BIC>" . $this->debtorWallet . "</BIC>";
        $res .= "</{$this->context->debtorWalletElementName}>" . PHP_EOL;
        $res .= "</DebtorWallet>" . PHP_EOL;

        return $res;
    }


    /**
     * @return string
     */
    public function XmlWrapDebtorAdditionalData(): string
    {
        if (count($this->_debtorAdditionalData) == 0) {
            return '';
        }

        $res = PHP_EOL . "<DebtorAdditionalData>" . PHP_EOL;


        foreach ($this->_debtorAdditionalData as $key => $value) {
            if (!in_array($key, $this->_possibleDebtorAdditionalDataKeys)) {
                continue;
            }

            // @todo: add specific regex pattern checks for value of each type.

            $res .= "<$key>";
            $res .= $value;
            $res .= "</$key>" . PHP_EOL;
        }
        $res .= "</DebtorAdditionalData>" . PHP_EOL;

        return $res;
    }

    /**
     * @throws Exception
     */
    public function addAdditionalData($key, $value): BluemRequest
    {
        if (!in_array($key, $this->_possibleDebtorAdditionalDataKeys)) {
            throw new Exception(
                "Incorrect key added as DebtorAdditionalData
                to request."
            );
        }

        $this->_debtorAdditionalData[$key] = $value;

        return $this; // allow function chaining
    }


    /**
     * @return mixed
     */
    public function RequestContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function RequestType(): string
    {
        return '';
    }


    /**
     * Perform sanitization of the description element
     *
     * @param String $description
     * @return string
     */
    protected function _sanitizeDescription(String $description) : string
    {
        // filter based on full list of invalid chars for description based on XSD
        // Wel toegestaan: -0-9a-zA-ZéëïôóöüúÉËÏÔÓÖÜÚ€ ()+,.@&=%"'/:;?$
        $description = preg_replace(
            '/[^-0-9a-zA-ZéëïôóöüúÉËÏÔÓÖÜÚ€ ()+,.@&=%\"\'\/:;?$]/',
            '',
            $description
        );
        // max 128 characters
        $result = substr($description, 0, 128);
        if ($result !== false) {
            return $result;
        } 
        return $description;
        
    }

    /*
    <DebtorAdditionalData>
    <EmailAddress>{0,1}</EmailAddress>
    <MobilePhoneNumber>{0,1}</MobilePhoneNumber>
    <CustomerProvidedDebtorIBAN>{0,1}</CustomerProvidedDebtorIBAN>
    <CustomerNumber>{0,1}</CustomerNumber>
    <CustomerName>{0,1}</CustomerName>
    <AttentionOf>{0,1}</AttentionOf>
    <Salutation>{0,1}</Salutation>
    <CustomerAddressLine1>{0,1}</CustomerAddressLine1>
    <CustomerAddressLine2>{0,1}</CustomerAddressLine2>
    <DebtorBankID>{0,1}</DebtorBankID>
    <DynamicData>{0,1}</DynamicData>
    </DebtorAdditionalData>
    */
}
