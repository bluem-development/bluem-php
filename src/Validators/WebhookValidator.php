<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

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

    abstract public function validate(string $data): self;
}
