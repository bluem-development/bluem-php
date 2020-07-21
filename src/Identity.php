<?php
/*
 * (c) Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Bluem\BluemPHP;

use Carbon\Carbon;

class IdentityBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $type_identifier = "createTransaction";
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

        $this->requestCategory = $this->getRequestCategoryElement($requestCategory);
        
        $this->description= $description;
        $this->debtorReference = $debtorReference;
        $this->debtorReturnURL = $debtorReturnURL;
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

    public function XmlString()
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

        /*
         <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<IdentityInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="TransactionRequest" mode="direct"
senderID="S1141" version="1.0" createDateTime="2019-05-02T08:28:40.314Z" messageCount="1"
xsi:noNamespaceSchemaLocation="../IdentityInterface.xsd">
    <IdentityTransactionRequest entranceCode="f33f6721cc197138b95a33566a0c388ec631d5b2" language="nl" brandID="IDIN" sendOption="none">
        <RequestCategory>
            <CustomerIDRequest action="request"/>
            <NameRequest action="request"/>
            <AddressRequest action="request"/>
            <BirthDateRequest action="request"/>
            <AgeCheckRequest ageOrOlder="18" action="skip"/>
            <GenderRequest action="request"/>
            <TelephoneRequest action="skip"/>
            <EmailRequest action="request"/>
        </RequestCategory>
        <Description>Identificatie voor demo</Description><!--description is shown to customer-->
        <DebtorReference>37083</DebtorReference><!-- optional; client reference/number -->
        <DebtorReturnURL automaticRedirect="1">https://companyname.demo.nl/idin/synchronise/f33f6721cc197138b95a33566a0c388ec631d5b2</DebtorReturnURL><!-- optional;return URL where Bluem redirects the user; if automaticRedirect=1 it means that the checkout result page is skipped, and the user is pushed back straight to given returnURL-->
    </IdentityTransactionRequest>
</IdentityInterface>
         */
    }
}


class IdentityStatusBluemRequest extends BluemRequest
{
    protected $xmlInterfaceName = "IdentityInterface";

    public $request_url_type = "ir";
    public $type_identifier = "requestStatus";
    public $transaction_code = "ISX";
    public function TransactionType() : String
    {
        return "ISX";
    }

    public function __construct($config, $entranceCode, $expectedReturn, $transactionID)
    {
        parent::__construct($config, $entranceCode, $expectedReturn);

        $this->transactionID = $transactionID;
    }


    public function XmlString()
    {
        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'StatusRequest',
            $this->XmlRequestObjectWrap(
                'IdentityStatusRequest',
                '<TransactionID>' . $this->transactionID . '</TransactionID>'
            )
        );

        /*            // Reference
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<IdentityInterface xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" type="StatusRequest" mode="direct"
senderID="S1141" version="1.0" createDateTime="2019-05-02T08:30:15.628Z" messageCount="1">
    <IdentityStatusRequest
            entranceCode="f33f6721cc197138b95a33566a0c388ec631d5b2">
        <TransactionID>c3a9d2d5477429fa</TransactionID>
    </IdentityStatusRequest>
</IdentityInterface>
*/
    }
}
