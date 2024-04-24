<?php

namespace Bluem\BluemPHP\Observability;

use Bluem\BluemPHP\Helpers\BluemConfiguration;
use JsonException;

final class SimpleFileIO {
    public function writeActivationFile(BluemConfiguration $data): bool
    {
        $path = __DIR__ . '/../../logs/';
        $filename = $this->createFileName();

        $allData = [
            'config'=>$data,
            'php_version'=>PHP_VERSION,
            'bluem_php_version'=>BLUEM_PHP_LIBRARY_VERSION
        ];

        try {
            $fileContent = json_encode($allData, JSON_THROW_ON_ERROR) . "\r\n";
        } catch (JsonException $e) {
            return false;
        }

        return file_put_contents($path . $filename, $fileContent, FILE_APPEND) !== false;
    }

    public function activationFileExists(): bool
    {
        $path = __DIR__ . '/../../logs/';
        $filename = $this->createFileName();

        return file_exists($path . $filename);
    }

    # consider a new file every month
    private function createFileName(): string
    {
        return "activations_" . date("Ym") . ".json";
    }
}

