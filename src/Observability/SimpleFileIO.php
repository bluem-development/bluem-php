<?php

namespace Bluem\BluemPHP\Observability;

use Bluem\BluemPHP\Helpers\BluemConfiguration;
use JsonException;

final class SimpleFileIO {
    public function writeActivationFile(BluemConfiguration $data): bool
    {
        $path = __DIR__ . '/../../logs/';
        $filename = "activations_" . date("Ymd") . ".json";

        try {
            $fileContent = json_encode($data, JSON_THROW_ON_ERROR) . "\r\n";
        } catch (JsonException $e) {
            return false;
        }

        return file_put_contents($path . $filename, $fileContent, FILE_APPEND) !== false;
    }

    public function activationFileExists(): bool
    {
        $path = __DIR__ . '/../../logs/';
        # consider a new file every month
        $filename = "activations_" . date("Ym") . ".json";

        return file_exists($path . $filename);
    }
}

