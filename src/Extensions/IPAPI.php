<?php

/**
 * (c) 2023 - Bluem Plugin Support <pluginsupport@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bluem\BluemPHP\Extensions;

use JsonException;

if (! defined("BLUEM_STATIC_IPAPI_KEY")) {
    define("BLUEM_STATIC_IPAPI_KEY", "ec7b6c41a0f51d87cfc8c53fcf64fe83");
}

/**
 * IPAPI Integration class:
 * goal of this integration is to allow IP Geolocation determination to filter request IPs
 * check https://ipstack.com for more information on the integration
 */
class IPAPI
{
    /**
     * @var bool
     */
    private bool $debug = false;

    /**
     * @var string
     */
    private string $baseURL = "http://api.ipstack.com/";

    /**
     * @var string
     */
    private string $accessKey = BLUEM_STATIC_IPAPI_KEY;

    /**
     * Verify if a given or server IP is country coded NL (and default to true in case of error)
     * reference: https://www.javatpoint.com/how-to-get-the-ip-address-in-php
     */
    public function CheckIsNetherlands(string $ip = ""): bool
    {
        $result = $this->QueryIP($ip);

        // if we encountered an error, return true for now
        if (
            isset($result['success'])
            && $result['success'] === false
        ) {
            return true;
        }

        // if we can't check for IP or the response is invalid, return true for now
        if (empty($result['country_code'])) {
            return true;
        }
        return ( $result['country_code'] === "NL" );
    }

    /**
     * Retrieve geolocation information of the given IP or if not given tries to infer the current IP.
     */
    public function QueryIP(string $ip = ""): mixed
    {
        // @todo Add IP datatype with validation
        if ($ip === "") {
            // @todo: move this to the ip class
            $ip = $this->GetCurrentIP();
        }

        $call_url = "{$this->baseURL}{$ip}?access_key=$this->accessKey";

        // Initialize CURL:
        $ch = curl_init(
            $call_url
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        try {
            $api_result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }

        // @todo: make request error handling prettier in the future

        return $api_result;
    }

    /**
     * Retrieve the current IP from the server, if possible
     */
    public function GetCurrentIP(): string
    {
        $ip = "";

        //whether ip is from the remote address
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //whether ip is from the share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //whether ip is from the proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ip;
    }
}
