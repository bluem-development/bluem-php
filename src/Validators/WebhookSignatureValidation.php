<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
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

        $public_key_file_path = dirname(__DIR__, 2) . self::KEY_FOLDER . $this->getKeyFileName();

        try {
            $xml = new DOMDocument();
            $xml->load($temp_file_path);

            $objDSig = new XMLSecurityDSig();

            $objDSig->locateSignature($xml);

            $objDSig->canonicalizeSignedInfo();

            // Validate the reference
            if (! $objDSig->validateReference()) {
                $this->addError("Reference validation failed");
            }

            // Load the public key
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            $key->loadKey($public_key_file_path, true, true);

            // Verify the signature
            if (! $objDSig->verify($key)) {
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

        // Check the datetime for certificates
        if ($this->env === BLUEM_ENVIRONMENT_TESTING && ( ( $current_date === "2023-06-28" && $current_time >= "08:00" ) || $current_date > "2023-06-28")) {
            $timestamp = '202306140200-202407050159';
        } elseif ($this->env === BLUEM_ENVIRONMENT_PRODUCTION && ( ( $current_date === "2023-07-04" && $current_time >= "08:00" ) || $current_date > "2023-07-04")) {
            $timestamp = '202306140200-202407050159';
        } else {
            $timestamp = '202206090200-202307110159';
        }
        return $prefix . $timestamp . '.pem';
    }
}
