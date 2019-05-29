<?php

namespace Omadonex\Support\Services\Centrifugo;

use Omadonex\Support\Interfaces\ICentrifugoClient;
use Omadonex\Support\Traits\CurlRequestTrait;

class CentrifugoClient implements ICentrifugoClient
{
    use CurlRequestTrait;

    protected $endpoint;
    protected $secret;
    protected $apiKey;

    /**
     * CentrifugoClient constructor.
     * @param $url
     * @param $apiKey
     */
    public function __construct($endpoint, $secret, $apiKey)
    {
        $this->endpoint = $endpoint;
        $this->secret = $secret;
        $this->apiKey = $apiKey;
    }

    /**
     * Url для коннекта через api
     * @return string
     */
    protected function getUrlApi()
    {
        return "{$this->endpoint}/api";
    }

    /**
     * Url для коннекта через socjhs
     * @param bool $sockJs
     * @return string
     */
    protected function getUrlSockJs($sockJs = false)
    {
        if ($sockJs) {
            return "{$this->endpoint}/connection/sockjs";
        }

        return $this->endpoint;
    }

    /**
     * Публикует данные на один канал
     * @param $channel
     * @param $data
     * @return bool|mixed|string
     * @throws \Omadonex\Support\Classes\Exceptions\OmxCurlRequestException
     */
    public function publish($channel, $data)
    {
        $body = json_encode(['method' => 'publish', 'params' => [
            'channel' => $channel,
            'data' => $data,
        ]]);

        return $this->sendCurlRequest($this->getUrlApi(), $body, $this->getHeaders());
    }

    /**
     * Вещает данные на несколько каналов
     * @param $channels
     * @param $data
     * @return bool|mixed|string
     * @throws \Omadonex\Support\Classes\Exceptions\OmxCurlRequestException
     */
    public function broadcast($channels, $data)
    {
        $body = json_encode(['method' => 'broadcast', 'params' => [
            'channels' => $channels,
            'data' => $data,
        ]]);

        return $this->sendCurlRequest($this->getUrlApi(), $body, $this->getHeaders());
    }

    /**
     * Формирует заголовки запроса
     * @return array
     */
    private function getHeaders()
    {
        return [
            'Content-Type: application/json',
            "Authorization: apikey {$this->apiKey}",
        ];
    }

    /**
     * Параметры соединения для использования на фронте
     * @param $userId
     * @param bool $sockJs
     * @param array $options
     * @return array
     */
    public function getConnectionParams($userId, $sockJs = false, $options = [])
    {
        return array_merge([
            'url' => $this->getUrlSockJs($sockJs),
            'user' => $userId,
            'timestamp' => time(),
            'token' => $this->generateToken(['sub' => $userId]),
        ], $options);
    }

    /**
     * Генерирует токен для использования на фронте
     * @param array $payload
     * @return string
     */
    private function generateToken($payload = [])
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = $this->convertToBase64Url($header);
        $base64UrlPayload = $this->convertToBase64Url(json_encode($payload));
        $signature = hash_hmac('sha256', "{$base64UrlHeader}.{$base64UrlPayload}", $this->secret, true);
        $base64UrlSignature = $this->convertToBase64Url($signature);

        return "{$base64UrlHeader}.{$base64UrlPayload}.{$base64UrlSignature}";
    }

    /**
     * @param $string
     * @return mixed
     */
    private function convertToBase64Url($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }
}