<?php

/*
 * (c) 2021 - Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

if (!defined("BLUEM_STATIC_IPAPI_KEY")) {
    define("BLUEM_STATIC_IPAPI_KEY", "ec7b6c41a0f51d87cfc8c53fcf64fe83");
}

/**
 * IPAPI Integration class
 */
class IPAPI
{
    private $_debug = false;

    private $_access_key = BLUEM_STATIC_IPAPI_KEY;

    /**
     * Retrieve geo-location information of the given IP or if not given tries to infer the current IP.
     *
     * @param string $ip
     * @return void
     */
    public function QueryIP($ip = "")
    {
        if ($ip=="") {
            $ip = $this->GetCurrentIP();
        }

        $base_url = "http://api.ipstack.com/";

        $call_url = "{$base_url}{$ip}?access_key={$this->_access_key}";

        // Initialize CURL:
        $ch = curl_init(
            $call_url
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $api_result = json_decode($json, true);

        if ($this->_debug == true) {
            var_dump($api_result);
        }

        // @todo: improve error handling

        return $api_result;
    }

    /**
     * Verify if a given or server IP is country coded NL (and default to true in case of error)
     * reference: https://www.javatpoint.com/how-to-get-the-ip-address-in-php
     *
     * @param string $ip
     * @return void
     */
    public function CheckIsNetherlands($ip ="")
    {
        $result = $this->QueryIP($ip);

        // if we encountered an error, return true for now
        if (isset($result['success'])
            && $result['success'] === false
        ) {
            return true;
        }
        // if we can't check for IP or the response is invalid, return true for now
        if (is_null($result['country_code'])) {
            return true;
        }

        return ($result['country_code'] === "NL");
    }

    /**
     * Retrieve the current IP from the server, if possible
     *
     * @return string
     */
    public function GetCurrentIP()
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
