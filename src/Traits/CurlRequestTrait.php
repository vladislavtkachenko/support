<?php

namespace Omadonex\Support\Traits;

use Omadonex\Support\Classes\Exceptions\OmxCurlRequestException;

trait CurlRequestTrait
{
    //TODO omadonex: сделать не только POST
    /**
     * Отправляет curl запрос
     * @param $url
     * @param $body
     * @param array $headers
     * @return bool|string
     * @throws OmxCurlRequestException
     */
    public function sendCurlRequest($url, $body, $headers = [], $connectTimeout = null, $timeout = null)
    {
        $ch = curl_init();
        if ($connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        }
        if ($timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.39.0');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (empty($info["http_code"]) || ($info["http_code"] !== 200)) {
            throw new OmxCurlRequestException($info['http_code'], $error);
        }

        return $data;
    }
}