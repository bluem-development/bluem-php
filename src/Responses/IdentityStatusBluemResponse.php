<?php

namespace Bluem\BluemPHP\Responses;

class IdentityStatusBluemResponse extends StatusBluemResponse {
    public static $transaction_type = "Identity";
    public static $response_primary_key = 'IdentityStatus';
    public static $error_response_type = 'IdentityErrorResponse';


    public function GetStatusCode(): ?string {
        if ( isset( $this->{$this->getParentXmlElement()}->Status ) ) {
            return $this->{$this->getParentXmlElement()}->Status . "";
        }

        return null;
    }


    public function GetIdentityReport(): ?IdentityStatusBluemResponse {
        if ( isset( $this->{$this->getParentXmlElement()}->IdentityReport ) ) {
            return $this->{$this->getParentXmlElement()}->IdentityReport;
        }

        return null;
    }

    public function GetAuthenticationAuthorityID(): ?string {
        if ( isset( $this->{$this->getParentXmlElement()}->AuthenticationAuthorityID ) ) {
            return $this->{$this->getParentXmlElement()}->AuthenticationAuthorityID . "";
        }

        return null;
    }
}
