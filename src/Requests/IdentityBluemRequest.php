<?php

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IdentityContext;

/**
 * IdentityBluemRequest object to request an Identity Transaction from the Bluem API.
 */
class IdentityBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "ITX";

    private $minAge = "18";

    // @todo: should be integer

    public function TransactionType(): string
    {
        return "ITX";
    }

    public function __construct(
        $config,
        $entranceCode,
        $expectedReturn,
        $requestCategory = [],
        $description = "",
        $debtorReference = "",
        $debtorReturnURL = ""
    )
    {
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID
        if (isset($config->IDINBrandID) && $config->IDINBrandID !== "") {

            $this->brandID = $config->IDINBrandID;
        } else {

            // @todo Throw an error when config is insufficiently setup
            $this->brandID = $config->brandID;
        }

        $this->requestCategory = $this->getRequestCategoryElement($requestCategory);

        $this->description = $description;
        $this->debtorReference = $debtorReference;
        $this->debtorReturnURL = $debtorReturnURL;

        $this->debtorReturnURL = $this->debtorReturnURL . "?transactionID={$this->debtorReference}";

        // @todo: make this a configurable setting
        $this->minAge = 18;
        // @todo: validate this , also based on XSD

        $this->context = new IdentityContext();

    }

    private function getIdinRequestCategory($category, $active = true)
    {
        $action = ($active ? "request" : "skip");

        $catstring = "";
        switch ($category) {
            case 'CustomerIDRequest':
                $catstring = '<CustomerIDRequest action="' . $action . '"/>';
                break;
            case 'NameRequest':
                $catstring = '<NameRequest action="' . $action . '"/>';
                break;
            case 'AddressRequest':
                $catstring = '<AddressRequest action="' . $action . '"/>';
                break;
            case 'BirthDateRequest':
                $catstring = '<BirthDateRequest action="' . $action . '"/>';
                break;
            case 'AgeCheckRequest': // this one is exclusive, cannot be combined
                $catstring = '<AgeCheckRequest ageOrOlder="' .
                    $this->minAge .
                    '" action="' . $action . '"/>';
                break;
            case 'GenderRequest':
                $catstring = '<GenderRequest action="' . $action . '"/>';
                break;
            case 'TelephoneRequest':
                $catstring = '<TelephoneRequest action="' . $action . '"/> ';
                break;
            case 'EmailRequest':
                $catstring = '<EmailRequest action="' . $action . '"/>';
                break;
            // CustomerIDLoginRequest login or DocumentSIgnatureRequest document sign request is exclusive, cannot be combined
            default:
                throw new \Exception("No proper IDIN request category given", 1);
                break;
        }

        return $catstring . '';
    }

    private function getRequestCategoryElement($active_categories = [])
    {
        $all_cats = [
            'CustomerIDRequest',
            'NameRequest',
            'AddressRequest',
            'BirthDateRequest',
            'AgeCheckRequest',
            'GenderRequest',
            'TelephoneRequest',
            'EmailRequest',
        ];

        // @todo: Add DocumentRequestSign & CustomerIDlogin later

        $result = "<RequestCategory>";

        foreach ($all_cats as $cat) {
            $result .= $this->getIdinRequestCategory(
                $cat,
                in_array(
                    $cat,
                    $active_categories
                )
            );
        }

        $result .= "</RequestCategory>";

        return '' . $result . '';
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
                $this->XmlWrapDebtorWallet(),
                [
                    'sendOption' => "none",
                    'language'   => "nl",
                    'brandID'    => $this->brandID,
                ]
            )
        );
    }
}
