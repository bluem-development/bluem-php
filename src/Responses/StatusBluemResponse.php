<?php

namespace Bluem\BluemPHP\Responses;

class StatusBluemResponse extends BluemResponse
{
    public function GetStatusCode(): ?string
    {
        if (isset($this->{$this->getParentXmlElement()}->Status)) {
            return $this->{$this->getParentXmlElement()}->Status . "";
        }

        return null;
    }

    protected function getParentXmlElement() : string
    {
        return static::$response_primary_key . "Update";
    }
}
