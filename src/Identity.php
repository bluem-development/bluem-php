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
    
    public function __construct($config,$entranceCode,$expectedReturn,$requestCategory = "CustomerIDRequest") {
        parent::__construct($config,$entranceCode,$expectedReturn);

        $this->requestCategory = $requestCategory;
    }


    private function getIdinRequestCategory($category="") {
        switch ($category) {
            case 'CustomerIDRequest':
                $cat = '<CustomerIDRequest action="request"/>';
                break;
            case 'NameRequest': 
                    $cat = '<NameRequest action="request"/>';
                break;
            case 'AddressRequest': 
                    $cat = '<AddressRequest action="request"/>';
                break;
            case 'BirthDateRequest': 
                    $cat = '<BirthDateRequest action="request"/>';
                break;
            case 'AgeCheckRequest': 
                    $cat = '<AgeCheckRequest ageOrOlder="18" action="skip"/>';
                break;
            case 'GenderRequest': 
                    $cat = '<GenderRequest action="request"/>';
                break;
            case 'TelephoneRequest': 
                    $cat = '<TelephoneRequest action="skip"/> ';
                break;
            case 'EmailRequest': 
                    $cat = '<EmailRequest action="request"/>';
                break;

            default:
                throw new \Exception("No proper IDIN request category given", 1);
                break;
        }
        return '<RequestCategory>'.$cat.'</RequestCategory>';
    }

    public function XmlString()
    {

        return $this->XmlRequestInterfaceWrap(
            $this->xmlInterfaceName,
            'TransactionRequest',
            $this->XmlRequestObjectWrap(
                'IdentityTransactionRequest',
                $this->getIdinRequestCategory($this->requestCategory).'
                <Description>' . $this->description . '</Description>
                <DebtorReference>' . $this->debtorReference . '</DebtorReference>
                <DebtorReturnURL automaticRedirect="1">' . $this->debtorReturnURL . '</DebtorReturnURL>',    
                [
                    'documentType'=>"PayRequest",
                    'sendOption'=>"none",
                    'language'=>"nl",
                    'brandID'=>$this->brandId
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

    public function __construct($config,$entranceCode,$expectedReturn,$transactionId) {
        parent::__construct($config,$entranceCode,$expectedReturn);

        $this->transactionId = $transactionId;
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