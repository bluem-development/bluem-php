<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class IdentityStatusBluemResponse extends StatusBluemResponse
{
    public static string $transaction_type = "Identity";
    public static string $response_primary_key = 'IdentityStatus';
    public static string $error_response_type = 'IdentityErrorResponse';

    public function GetIdentityReport(): ?IdentityStatusBluemResponse
    {
        $parent = $this->getParentElement();

        if ($parent && !empty($parent->IdentityReport)) {
            return $this->{$this->getParentXmlElement()}->IdentityReport;
        }

        return null;
    }

    public function GetAuthenticationAuthorityID(): string
    {
        return $this->getParentStringVariable('AuthenticationAuthorityID');
    }
}
