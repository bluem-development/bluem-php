<?php

namespace Bluem\BluemPHP\Validators;

use SimpleXMLElement;

class WebhookXmlValidation extends WebhookValidator
{
    private SimpleXMLElement $xmlObject;
    private string $senderID;
    
    private const ALLOWED_SERVICE_INTERFACES = ['EPaymentInterface'];
    
    
    public function __construct(SimpleXMLElement $xmlObject, string $senderID) {
        $this->xmlObject = $xmlObject;
        $this->senderID = $senderID;
    }
    
    public function validate(): WebhookXmlValidation
    {

        $serviceInterface = $this->xmlObject->children()[0];
        if (!in_array($serviceInterface->getName(), self::ALLOWED_SERVICE_INTERFACES)) {
            $this->addError("Invalid service interface name: " . $serviceInterface->getName());
        }

        $givenSenderID = ((string)$serviceInterface->attributes()['senderID']);
        if ($this->senderID !== $givenSenderID) {
            $this->addError("Invalid senderID");
        }
        if ((string)$serviceInterface->attributes()['type'] !== "StatusUpdate") {
            $this->addError("Invalid service interface type attribute");
        }

        if ((int)$serviceInterface->attributes()['messageCount'] !== 1) {
            $this->addError("Invalid service interface messageCount attribute");
        }
        
        if ( ! $this->xmlObject->Signature->SignatureValue ) {
            $this->addError("Invalid Signature Value");
        }
        
        if ( ! $this->xmlObject->Signature->KeyInfo->KeyName ) {
            $this->addError("Invalid KeyName");
        }
        
        return $this;
    }
}