<?php

namespace Bluem\BluemPHP\Contexts;

abstract class BluemContext {

    public array $BICs;

    public function __construct( array $BICs = [] ) {
        $this->BICs = $BICs;
    }

    public function getBICs(): array {
        return $this->BICs;
    }

    public function getBICCodes(): array {
        $codes = [];
        foreach ( $this->BICs as $BIC ) {
            $codes[] = $BIC->issuerID;
        }

        return $codes;
    }

    public function getValidationSchema(): string {
        return __DIR__ . '/../../validation/';
    }
}
