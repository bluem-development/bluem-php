<?php

namespace Bluem\BluemPHP\Validators;

abstract class WebhookValidator implements WebhookValidatorInterface
{
    public static bool $isValid = true;
    
    /* @var string[] */
    protected static array $errors = [];

    protected function addError($error): void
    {
        self::$isValid = false;
        self::$errors[] = $error;
    }
    
    public function errorMessage(): string 
    {
        return "Validation fails: " 
            . implode(', ', self::$errors);
    }


}