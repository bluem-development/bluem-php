<?php

namespace Bluem\BluemPHP\Responses;

class TransactionBluemResponse extends BluemResponse
{
    public function GetTransactionURL()
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionURL)) ? $this->{$this->getParentXmlElement()}->TransactionURL . "" : null;
    }

    public function GetTransactionID()
    {
        return (isset($this->{$this->getParentXmlElement()}->TransactionID)) ? $this->{$this->getParentXmlElement()}->TransactionID . "" : null;
    }

    protected function getParentXmlElement()
    {
        return static::$response_primary_key . "Response";
    }
}
