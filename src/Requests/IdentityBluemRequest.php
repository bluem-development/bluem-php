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
    ) {
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

        $this->debtorReturnURL = $this->debtorReturnURL . "?debtorReference={$this->debtorReference}";

        // @todo: make this a configurable setting
        $this->minAge = 18;
        // @todo: validate this , also based on XSD

        $this->context = new IdentityContext();
    }

    /**
     * @param      $category
     * @param bool $active
     *
     * @return string
     * @throws \Exception
     */
    private function getIdinRequestCategory($category, $active = true)
    {
        $action = ($active ? "request" : "skip");

        switch ($category) {
            case 'CustomerIDRequest':
                return "<CustomerIDRequest action=\"{$action}\"/>" . '';
            case 'NameRequest':
                return "<NameRequest action=\"{$action}\"/>" . '';
            case 'AddressRequest':
                return "<AddressRequest action=\"{$action}\"/>" . '';
            case 'BirthDateRequest':
                return "<BirthDateRequest action=\"{$action}\"/>" . '';
            case 'GenderRequest':
                return "<GenderRequest action=\"{$action}\"/>" . '';
            case 'TelephoneRequest':
                return "<TelephoneRequest action=\"{$action}\"/>" . '';
            case 'EmailRequest':
                return "<EmailRequest action=\"{$action}\"/>" . '';

            // exclusive categories, cannot be combined!
            case 'AgeCheckRequest':
                return "<AgeCheckRequest ageOrOlder=\"{$this->minAge}\" action=\"{$action}\"/>" . '';
            case 'CustomerIDLoginRequest':
                return "<CustomerIDLoginRequest action=\"{$action}\"/>" . '';
            // TODO: Add DocumentSignatureRequest (exclusive)

            // default: Throw error.
            default:
                throw new \Exception("No proper IDIN request category given", 1);
        }
    }

    /**
     * @param array $active_categories
     *
     * @return string
     * @throws \Exception
     */
    private function getRequestCategoryElement($active_categories = [])
    {
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
}
