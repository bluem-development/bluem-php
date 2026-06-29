<?php

declare(strict_types=1);

namespace Bluem\BluemPHP\Transport;

use RuntimeException;

final class CurlHttpTransport implements HttpTransportInterface
{
    public function send(string $url, array $headers, string $body): HttpTransportResponse
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new RuntimeException('Unable to initialize cURL handle');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errorMessage = curl_error($curl);

        curl_close($curl);

        if ($response === false) {
            $message = $errorMessage !== '' ? $errorMessage : 'Unknown cURL error';
            throw new RuntimeException($message);
        }

        return new HttpTransportResponse($statusCode, (string) $response);
    }
}
