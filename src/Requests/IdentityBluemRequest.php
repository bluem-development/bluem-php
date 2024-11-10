<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Requests;

use Bluem\BluemPHP\Contexts\IdentityContext;
use Bluem\BluemPHP\Exceptions\InvalidBluemRequestException;
use Exception;

define("BLUEM_DEFAULT_MIN_AGE", 18);

/**
 * IdentityBluemRequest object to request an Identity Transaction from the Bluem API.
 */
class IdentityBluemRequest extends BluemRequest
{
    public $minage;
    public $request_url_type = "ir";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "ITX";
    protected $xmlInterfaceName = "IdentityInterface";
    /**
     * @var int
     */
    private $minAge;

    private string $requestCategory;
    private string $description;
    private string $debtorReturnURL;

    /**
     * @param $config
     * @param $entranceCode
     * @param $expectedReturn
     * @param array  $requestCategory
     * @param string $description
     * @param string $debtorReference
     * @param string $debtorReturnURL
     *
     * @throws Exception
     */
    public function __construct(
        $config,
        $entranceCode,
        $expectedReturn,
        array $requestCategory = [],
        string $description = "",
        private string $debtorReference = "",
        $debtorReturnURL = ""
    ) {
        parent::__construct($config, $entranceCode, $expectedReturn);
        // @todo: verify return URL can no longer be set in IdentityBluemRequest construction, instead it is created in the config

        // override specific brand ID
        $this->brandID = isset($config->IDINBrandID) && $config->IDINBrandID !== "" ? $config->IDINBrandID : $config->brandID;

        $this->requestCategory = $this->getRequestCategoryElement($requestCategory);
        $this->description     = $this->_sanitizeDescription($description);
        if (empty($debtorReturnURL)) {
            throw new InvalidBluemRequestException("Debtor return URL is required");
        }
        $this->debtorReturnURL = $debtorReturnURL . "?debtorReference=$this->debtorReference";

        // @todo: make this a configurable setting
        $this->minAge = $config->minAge ?? BLUEM_DEFAULT_MIN_AGE;
        // @todo: validate this , also based on XSD

        $this->context = new IdentityContext();
        // @todo: decide whether or not to use the context pattern
    }

    /**
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

        return $result . "</RequestCategory>";
    }

    /**
     * @param $category
     *
     * @throws Exception
     */
    private function getIdinRequestCategory($category, bool $active = true): string
    {
        $action = ( $active ? "request" : "skip" );

        return match ($category) {
            'CustomerIDRequest' => "<CustomerIDRequest action=\"$action\"/>",
            'NameRequest' => "<NameRequest action=\"$action\"/>",
            'AddressRequest' => "<AddressRequest action=\"$action\"/>",
            'BirthDateRequest' => "<BirthDateRequest action=\"$action\"/>",
            'GenderRequest' => "<GenderRequest action=\"$action\"/>",
            'TelephoneRequest' => "<TelephoneRequest action=\"$action\"/>",
            'EmailRequest' => "<EmailRequest action=\"$action\"/>",
            'AgeCheckRequest' => "<AgeCheckRequest ageOrOlder=\"" .
                   $this->getMinAge() .
                   "\" action=\"$action\"/>",
            'CustomerIDLoginRequest' => "<CustomerIDLoginRequest action=\"$action\"/>",
            default => throw new Exception("No proper iDIN request category given", 1),
        };
    }

    private function getMinAge(): string
    {
        return "" . ( $this->minage ?? BLUEM_DEFAULT_MIN_AGE );
    }

    public function TransactionType(): string
    {
        return "ITX";
    }

    public function XmlString(): string
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IdentityTransactionRequest',
                ( $this->requestCategory ) . '
                <Description>' . $this->description . '</Description>
                <DebtorReference>' . $this->debtorReference . '</DebtorReference>
                <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>' .
                $this->XmlWrapDebtorWalletForPaymentMethod() .
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
     * EntranceCodes for iDIN starting with the prefix `showConsumerGui`,
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
        $this->entranceCode = "showConsumerGui" .
                              substr($this->entranceCode, 0, 25);
    }

    private function XmlWrapDebtorWalletForPaymentMethod(): string
    {
        $res = '';

        if ($this->context->isIDIN()) {
            $bic = '';

            if (empty($this->context->getPaymentDetail('BIC'))) {
                if (!empty($this->debtorWallet)) {
                    $bic = $this->debtorWallet;
                }
            } else {
                $bic = $this->context->getPaymentDetail('BIC');
            }

            if (empty($bic)) {
                return '';
            }

            $res = PHP_EOL . "<DebtorWallet>" . PHP_EOL;
            $res .= "<{$this->context->debtorWalletElementName}>";
            $res .= "<BIC>" . $bic . "</BIC>";
            $res .= "</{$this->context->debtorWalletElementName}>" . PHP_EOL;

            return $res . ("</DebtorWallet>" . PHP_EOL);
        }
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

        if (! in_array($BIC, $this->context->getBICCodes())) {
            throw new Exception("Invalid BIC code given, should be a valid BIC of a supported bank.");
        }

        $this->debtorWallet = $BIC;
    }
}
