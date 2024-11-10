<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class StatusBluemResponse extends BluemResponse
{
    public function GetStatusCode(): string
    {
        return $this->getParentStringVariable("Status");
    }

    protected function getParentXmlElement(): string
    {
        return static::$response_primary_key . "Update";
    }
}
