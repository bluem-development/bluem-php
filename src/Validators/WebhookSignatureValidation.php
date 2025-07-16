<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Bluem\BluemPHP\Helpers\Now;
use Exception;
use Selective\XmlDSig\CryptoVerifier;
use Selective\XmlDSig\PublicKeyStore;
use Selective\XmlDSig\XmlSignatureVerifier;

class WebhookSignatureValidation extends WebhookValidator
{
    private const KEY_FOLDER = "/keys/";

    public function __construct(
        private string $env
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

        $publicKeyStore = new PublicKeyStore();

        $public_key_file_path = dirname(__DIR__, 2) . self::KEY_FOLDER . $this->getKeyFileName();

        try {
            $publicKeyStore->loadFromPem(file_get_contents($public_key_file_path));
            $cryptoVerifier = new CryptoVerifier($publicKeyStore);

            // Create a verifier instance and pass the crypto decoder
            $xmlSignatureVerifier = new XmlSignatureVerifier($cryptoVerifier);

            // Verify a XML file
            $xmlVerified = $xmlSignatureVerifier->verifyXml(file_get_contents($temp_file_path));
            if (! $xmlVerified) {
                $this->addError("Invalid signature");
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        fclose($temp_file);

        return $this;
    }

    /**
     * Determine filename certificate
     */
    private function getKeyFileName(): string
    {
        // Define current date & time
        $now = new Now();
        $current_date = $now->format('Y-m-d');
        $current_time = $now->format('H:i');

        // Define the default filename
        $prefix = 'webhook_bluem_nl_';

        // 2025 certificate on testing from July 17th, 8:30 CET time
        if ( (  $current_date === "2024-07-17" && $current_time >= "6:30" ) || $current_date > "2024-07-17") {
            $timestamp = '20250717';
        } elseif ( (  $current_date === "2024-07-01" && $current_time >= "12:00" ) || $current_date > "2024-07-01") {
            $timestamp = '20240701';
        } elseif ($this->env === BLUEM_ENVIRONMENT_TESTING && ( ( $current_date === "2023-06-28" && $current_time >= "08:00" ) || $current_date > "2023-06-28")) {
            $timestamp = '202306140200-202407050159';
        } elseif ($this->env === BLUEM_ENVIRONMENT_PRODUCTION && ( ( $current_date === "2023-07-04" && $current_time >= "08:00" ) || $current_date > "2023-07-04")) {
            $timestamp = '202306140200-202407050159';
        } else {
            $timestamp = '202206090200-202307110159';
        }

        return $prefix . $timestamp . '.pem';
    }
}
