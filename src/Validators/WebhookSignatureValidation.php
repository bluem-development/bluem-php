<?php

/**
 * © 2026 - Bluem Payment & Identity: https://bluem.nl
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Bluem\BluemPHP\Helpers\Now;
use DOMDocument;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class WebhookSignatureValidation extends WebhookValidator
{
    private const string KEY_FOLDER = "/keys/";

    public function __construct(
        private readonly string $env
    ) {
    }

    /**
     * Validate webhook signature based on a key file
     * available in the `keys` folder.
     */
    public function validate(string $data): self
    {
        $temp_file = tmpfile();
        fwrite($temp_file, $data);
        $temp_file_path = stream_get_meta_data($temp_file)['uri'];

        $doc = new DOMDocument();
        $doc->load($temp_file_path);

        $objDSig = new XMLSecurityDSig();

        try {
            $objDSig->locateSignature($doc);
            $objDSig->canonicalizeSignedInfo();
            $objDSig->validateReference();
        } catch (Exception $e) {
            $this->addError('Reference Validation Failed: ' . $e->getMessage());
            fclose($temp_file);
            return $this;
        }

        try {
            $objKey = $objDSig->locateKey();
            if (! $objKey instanceof XMLSecurityKey) {
                $this->addError('Unable to determine signature key algorithm');
                fclose($temp_file);
                return $this;
            }
            $objKey->loadKey($this->getPublicKeyFilePath(), true);
        } catch (Exception) {
            $this->addError('Could not load public key');
            fclose($temp_file);
            return $this;
        }

        try {
            if ($objDSig->verify($objKey) !== 1) {
                $this->addError("Invalid signature");
            }
        } catch (Exception $exception) {
            $this->addError($exception->getMessage());
        }

        fclose($temp_file);

        return $this;
    }

    /**
     * Determine full path to the public key/certificate used for validation.
     */
    protected function getPublicKeyFilePath(): string
    {
        return dirname(__DIR__, 2) . self::KEY_FOLDER . $this->getKeyFileName();
    }

    /**
     * Determine filename certificate.
     */
    private function getKeyFileName(): string
    {
        $now = $this->getNow();
        $current_date = $now->format('Y-m-d');
        $current_time = $now->format('H:i');

        $timestamp = '202206090200-202307110159';

        if ($this->uses2026Certificate($current_date, $current_time)) {
            $timestamp = '20260716';
        } elseif ($this->uses2025Certificate($current_date, $current_time)) {
            $timestamp = '20250717';
        } elseif ($this->uses2024Certificate($current_date, $current_time)) {
            $timestamp = '20240701';
        } elseif ($this->uses2023Certificate($current_date, $current_time)) {
            $timestamp = '202306140200-202407050159';
        }

        return 'webhook_bluem_nl_' . $timestamp . '.pem';
    }

    private function uses2026Certificate(string $current_date, string $current_time): bool
    {
        if (
            $this->env === BLUEM_ENVIRONMENT_PRODUCTION
            && (
                ($current_date === '2026-07-20' && $current_time >= '09:00')
                || $current_date > '2026-07-20'
            )
        ) {
            return true;
        }

        return (
            $this->env === BLUEM_ENVIRONMENT_TESTING
            || $this->env === BLUEM_ENVIRONMENT_ACCEPTANCE
        ) && (
            ($current_date === '2026-07-16' && $current_time >= '13:00')
            || $current_date > '2026-07-16'
        );
    }

    protected function getNow(): Now
    {
        return new Now();
    }

    private function uses2025Certificate(string $current_date, string $current_time): bool
    {
        if (
            $this->env === BLUEM_ENVIRONMENT_PRODUCTION
            && (
                ($current_date === '2025-07-18' && $current_time >= '08:30')
                || $current_date > '2025-07-18'
            )
        ) {
            return true;
        }

        return (
            $this->env === BLUEM_ENVIRONMENT_TESTING
            || $this->env === BLUEM_ENVIRONMENT_ACCEPTANCE
        ) && (
            ($current_date === '2025-07-17' && $current_time >= '08:30')
            || $current_date > '2025-07-17'
        );
    }

    private function uses2024Certificate(string $current_date, string $current_time): bool
    {
        return ($current_date === '2024-07-01' && $current_time >= '12:00')
            || $current_date > '2024-07-01';
    }

    private function uses2023Certificate(string $current_date, string $current_time): bool
    {
        if (
            $this->env === BLUEM_ENVIRONMENT_TESTING
            && (
                ($current_date === '2023-06-28' && $current_time >= '08:00')
                || $current_date > '2023-06-28'
            )
        ) {
            return true;
        }

        return $this->env === BLUEM_ENVIRONMENT_PRODUCTION
            && (
                ($current_date === '2023-07-04' && $current_time >= '08:00')
                || $current_date > '2023-07-04'
            );
    }
}
