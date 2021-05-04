<?php

namespace Bluem\BluemPHP\Responses;

class IdentityStatusBluemResponse extends StatusBluemResponse
{
    public static $transaction_type = "Identity";
    public static $response_primary_key = "Identity" . "Status";
    public static $error_response_type = "Identity" . "ErrorResponse";


    public function GetStatusCode()
    {
        if (isset($this->{$this->getParentXmlElement()}->Status)) {
            return $this->{$this->getParentXmlElement()}->Status . "";
        }

        return null;
    }


    public function GetIdentityReport()
    {
        if (isset($this->{$this->getParentXmlElement()}->IdentityReport)) {
            return $this->{$this->getParentXmlElement()}->IdentityReport;
        }

        return null;
    }

    public function GetAuthenticationAuthorityID()
    {
        if (isset($this->{$this->getParentXmlElement()}->AuthenticationAuthorityID)) {
            return $this->{$this->getParentXmlElement()}->AuthenticationAuthorityID . "";
        }

        return null;
    }
}
