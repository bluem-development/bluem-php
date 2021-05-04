<?php

namespace Bluem\BluemPHP\Responses;

class StatusBluemResponse extends BluemResponse
{
    public function GetStatusCode()
    {
        $parent_key = $this->getParentXmlElement();
        $child_key = $this->getChildXmlElement();

        if (isset($this->{$parent_key}->{$child_key}->Status)) {
            return $this->{$parent_key}->{$child_key}->Status . "";
        }

        return null;
    }

    protected function getParentXmlElement()
    {
        return static::$response_primary_key . "Update";
    }
}
