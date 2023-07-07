<?php
/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Validators;

use Carbon\Carbon;
use Exception;
use Selective\XmlDSig\XmlSignatureValidator;

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

        $signatureValidator = new XmlSignatureValidator();

        $public_key_file_path = dirname(__DIR__, 2) . self::KEY_FOLDER . $this->getKeyFileName();

        try {
            $signatureValidator->loadPublicKeyFile($public_key_file_path);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        $xmlVerified = $signatureValidator->verifyXmlFile($temp_file_path);
        if (! $xmlVerified) {
            $this->addError("Invalid signature");
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
        $now = Carbon::now()->timezone('Europe/Amsterdam');
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
