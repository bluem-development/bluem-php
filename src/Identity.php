<?php
/**
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;


class IdentityBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $typeIdentifier = "createTransaction";
    public $transaction_code = "ITX";
    
    public function TransactionType() : String
    {
        return "ITX";
    }
    
    public function __construct(
        $config,
        $entranceCode,
        $expectedReturn,
        $requestCategory = [],
        $description="",
        $debtorReference="",
        $debtorReturnURL = ""
    ) {
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID
        if (isset($config->IDINBrandID) && $config->IDINBrandID!=="") {

            $this->brandID = $config->IDINBrandID;
        } else {
            $this->brandID = $config->brandID;
        }
        
        $this->requestCategory = $this->getRequestCategoryElement($requestCategory);
        
        $this->description= $description;
        $this->debtorReference = $debtorReference;
        $this->debtorReturnURL = $debtorReturnURL;

        $this->debtorReturnURL = $this->debtorReturnURL . "?transactionID={$this->debtorReference}";
    }
    
    private function getIdinRequestCategory($category,$active=true)
    {
        $action = ($active?"request":"skip");
        
        $catstring = "";
        switch ($category) {
            case 'CustomerIDRequest':
                $catstring = '<CustomerIDRequest action="'.$action.'"/>';
                break;
            case 'NameRequest':
                    $catstring = '<NameRequest action="'.$action.'"/>';
                break;
            case 'AddressRequest':
                    $catstring = '<AddressRequest action="'.$action.'"/>';
                break;
            case 'BirthDateRequest':
                    $catstring = '<BirthDateRequest action="'.$action.'"/>';
                break;
            case 'AgeCheckRequest': // this one is exclusive, cannot be combined
                    $catstring = '<AgeCheckRequest ageOrOlder="18" action="'.$action.'"/>';
                break;
            case 'GenderRequest':
                    $catstring = '<GenderRequest action="'.$action.'"/>';
                break;
            case 'TelephoneRequest':
                    $catstring = '<TelephoneRequest action="'.$action.'"/> ';
                break;
            case 'EmailRequest':
                    $catstring = '<EmailRequest action="'.$action.'"/>';
                break;
                // CustomerIDLoginRequest login or DocumentSIgnatureRequest document sign request is exclusive, cannot be combined
            default:
                throw new \Exception("No proper IDIN request category given", 1);
                break;
        }
        return $catstring.'';
    }
    private function getRequestCategoryElement($active_categories=[])
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

        // TODO: Add DocumentRequestSign & CustomerIDlogin later

        $result = "<RequestCategory>";

        foreach ($all_cats as $cat ) {
            $result.= $this->getIdinRequestCategory($cat,in_array($cat,$active_categories));
        }
        
        $result.="</RequestCategory>";

        return ''.$result.'';
    }

    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IdentityTransactionRequest',
                ($this->requestCategory).'
                <Description>' . $this->description . '</Description>
                <DebtorReference>' . $this->debtorReference . '</DebtorReference>
                <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>',
                [
                    'sendOption'=>"none",
                    'language'=>"nl",
                    'brandID'=>$this->brandID
                ]
            )
        );
   }
}


class IdentityStatusBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $typeIdentifier = "requestStatus";
    public $transaction_code = "ISX";
    public function TransactionType() : String
    {
        return "ISX";
    }

    public function __construct($config, $entranceCode, $expectedReturn, $transactionID)
    {
        parent::__construct($config, $entranceCode, $expectedReturn);

        // override specific brand ID
        if (isset($config->IDINBrandID) && $config->IDINBrandID!=="") {

            $this->brandID = $config->IDINBrandID;
        } else {
            $this->brandID = $config->brandID;
        }
        // $this->brandID = $config->IDINBrandID;

        $this->transactionID = $transactionID;
    }


    public function XmlString() : String
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'IdentityStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );

        

    }
}
