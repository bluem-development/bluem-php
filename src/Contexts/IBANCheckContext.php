<?php
/*
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Contexts;

class IBANCheckContext extends BluemContext {
    public function getValidationSchema(): string {
        return parent::getValidationSchema() . 'IBANCheck.xsd';
    }
}
