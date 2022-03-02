<?php

namespace Bluem\BluemPHP\Contexts;

class IBANCheckContext extends BluemContext {
    // no context preset yet

    public function getValidationSchema(): string {
        return parent::getValidationSchema() . 'IBANCheck.xsd';
    }
}
