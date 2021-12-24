<?php

namespace Bluem\BluemPHP\Responses;

class TransactionBluemResponse extends BluemResponse
{
    public function GetTransactionURL(): ?string
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionURL)) ? $this->{$this->getParentXmlElement()}->TransactionURL . "" : null;
    }

    public function GetTransactionID(): ?string
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionID)) ? $this->{$this->getParentXmlElement()}->TransactionID . "" : null;
    }

    public function GetDebtorReference(): ?string
    {
        return (isset($this->{$this->getParentXmlElement()}->DebtorReference)) ? $this->{$this->getParentXmlElement()}->DebtorReference . "" : null;
    }

    protected function getParentXmlElement() : string
    {
        return static::$response_primary_key . "Response";
    }
}
