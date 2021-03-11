<?php

/*
 * (c) 2020 - Daan Rijpkema <info@daanrijpkema.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP;


class BIC {

    public $issuerID;
    public $issuerName;

    public function __construct(
        $issuerID, $issuerName
    ) {
        $this->issuerID = $issuerID;
        $this->issuerName = $issuerName;
    }
}


