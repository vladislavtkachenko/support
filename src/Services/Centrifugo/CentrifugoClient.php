<?php

namespace Omadonex\Support\Services\Centrifugo;

use Omadonex\Support\Interfaces\ICentrifugoClient;
use Omadonex\Support\Traits\CurlRequestTrait;

class CentrifugoClient implements ICentrifugoClient
{
    use CurlRequestTrait;

    protected $url;
    protected $apiKey;

    /**
     * CentrifugoClient constructor.
     * @param $url
     * @param $apiKey
     */
    public function __construct($url, $apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
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

        return $this->sendCurlRequest($this->url, $body, $this->getHeaders());
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

        return $this->sendCurlRequest($this->url, $body, $this->getHeaders());
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
}