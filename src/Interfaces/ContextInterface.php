<?php

namespace Bluem\BluemPHP\Interfaces;

interface ContextInterface
{
    public function getIssuers(): array;

    public function getBICCodes(): array;
    
    public function getValidationSchema(): string;
    
    public function getDebtorWalletElementName(): string;
    
}