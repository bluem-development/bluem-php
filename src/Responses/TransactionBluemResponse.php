<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Responses;

class TransactionBluemResponse extends BluemResponse {

    public function GetTransactionURL(): string {
        return $this->getParentStringVariable("TransactionURL");
    }

    protected function getParentXmlElement(): string {
        return static::$response_primary_key . "Response";
    }

    public function GetTransactionID(): string {
        return $this->getParentStringVariable("TransactionID");
    }

    public function GetDebtorReference(): string {
        return $this->getParentStringVariable("DebtorReference");
    }
}
