<?php

namespace Bluem\BluemPHP\Validators;

abstract class WebhookValidator implements WebhookValidatorInterface
{
    public bool $isValid = true;
    public array $errors = [];


    protected function addError($error): void
    {
        $this->isValid = false;
        $this->errors[] = $error;
    }


}