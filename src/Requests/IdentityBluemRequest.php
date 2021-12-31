<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IdentityContext;
use Bluem\BluemPHP\Interfaces\BluemRequestInterface;
use Exception;

define("BLUEM_DEFAULT_MIN_AGE", 18);

/**
 * IdentityBluemRequest object to request an Identity Transaction from the Bluem API.
 */
class IdentityBluemRequest extends BluemRequest implements BluemRequestInterface
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "ITX";

    /** 
     * @var int  
     */
    private $minAge;
    
    /**
     * @var string
     */
    private $requestCategory;
    /**
     * @var string
     */
    private $description;
    /**
     * @var mixed|string
     */
    private $debtorReference;
    /**
     * @var string
     */
    private $debtorReturnURL;
    /**
     * @var string
     */
    private $brandID;
// @todo: deprecated, remove
    public function TransactionType(): string
    {
        return "ITX";
    }

    /**
     * @param $config
     * @param $entranceCode
     * @param $expectedReturn
     * @param array $requestCategory
     * @param string $description
     * @param string $debtorReference
     * @throws Exception
     */
    public function __construct(
        $config,
        $entranceCode,
        $expectedReturn,
        array $requestCategory = [],
        string $description = "",
        string $debtorReference = "",
        $debtorReturnURL = ""
    ) {
        // @todo: verify return URL can no longer be set in IdentityBluemRequest construction, instead it is created in the config
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID
        if (isset($config->IDINBrandID) && $config->IDINBrandID !== "") {
            $this->brandID = $config->IDINBrandID;
        } else {

            // @todo Throw an error when config is insufficiently setup
            $this->brandID = $config->brandID;
        }

        $this->requestCategory = $this->getRequestCategoryElement($requestCategory);
        $this->description = $this->_sanitizeDescription($description);
        

        $this->debtorReference = $debtorReference;
        if($debtorReturnURL=="") {
            throw new Exception("Debtor return URL is required");
        }
        $this->debtorReturnURL = $debtorReturnURL."?debtorReference=$this->debtorReference";

        // @todo: make this a configurable setting
        $this->minAge = BLUEM_DEFAULT_MIN_AGE;
    
        // @todo: validate this , also based on XSD

        $this->context = new IdentityContext();
    }

    /**
     * @param      $category
     * @param bool $active
     *
     * @return string
     * @throws Exception
     */
    private function getIdinRequestCategory($category, bool $active = true): string
    {
        $action = ($active ? "request" : "skip");

        switch ($category) {
            case 'CustomerIDRequest':
                return "<CustomerIDRequest action=\"$action\"/>";
            case 'NameRequest':
                return "<NameRequest action=\"$action\"/>";
            case 'AddressRequest':
                return "<AddressRequest action=\"$action\"/>";
            case 'BirthDateRequest':
                return "<BirthDateRequest action=\"$action\"/>";
            case 'GenderRequest':
                return "<GenderRequest action=\"$action\"/>";
            case 'TelephoneRequest':
                return "<TelephoneRequest action=\"$action\"/>";
            case 'EmailRequest':
                return "<EmailRequest action=\"$action\"/>";

            // exclusive categories, cannot be combined!
            case 'AgeCheckRequest':
                return "<AgeCheckRequest ageOrOlder=\"".($this->getMinAge()?? 18)."\" action=\"$action\"/>";
            case 'CustomerIDLoginRequest':
                return "<CustomerIDLoginRequest action=\"$action\"/>";
            // @todo: Add DocumentSignatureRequest (exclusive)

            // default: Throw error.
            default:
                throw new Exception("No proper iDIN request category given", 1);
                // @todo: add our own exception class 
        }
    }
    
    private function getMinAge(): string
    {
        return "". ($this->minage ?? BLUEM_DEFAULT_MIN_AGE);
    }

    /**
     * @param array $active_categories
     *
     * @return string
     * @throws Exception
     */
    private function getRequestCategoryElement(array $active_categories = []): string
    {
        // @todo perform more validation on active categories?
        
        $all_cats = [
            'CustomerIDRequest',
            'CustomerIDLoginRequest',
            'NameRequest',
            'AddressRequest',
            'BirthDateRequest',
            'AgeCheckRequest',
            'GenderRequest',
            'TelephoneRequest',
            'EmailRequest',
        ];

        // @todo: Add DocumentRequestSign later (add after EmailRequest).

        $result = "<RequestCategory>";

        foreach ($all_cats as $cat) {
            $result .= $this->getIdinRequestCategory(
                $cat,
                in_array(
                    $cat,
                    $active_categories
                )
            );
            // @todo deal with possible exception here
        }

        $result .= "</RequestCategory>";

        return $result;
    }

    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IdentityTransactionRequest',
                ($this->requestCategory) . '
                <Description>' . $this->description . '</Description>
                <DebtorReference>' . $this->debtorReference . '</DebtorReference>
                <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>' .
                $this->XmlWrapDebtorWallet() .
                $this->XmlWrapDebtorAdditionalData(),
                [
                    'sendOption' => "none",
                    'language'   => "nl",
                    'brandID'    => $this->brandID,
                ]
            )
        );
    }

    /**
     * EntranceCodes for iDIN starting with the prefix 'showConsumerGui,
     * will always get to a test status page
     * of the bank where you can choose which status you want to receive back.
     *
     * This does clip your current entranceCode
     * to ensure the max length is respected.
     *
     * @return void
     */
    public function enableStatusGUI()
    {
        $this->entranceCode = "showConsumerGui".
            substr($this->entranceCode, 0, 25);
    }
}
