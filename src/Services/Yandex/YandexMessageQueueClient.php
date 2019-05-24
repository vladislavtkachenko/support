<?php

namespace Omadonex\Support\Services\Yandex;

use Omadonex\Support\Interfaces\IMessageQueueClient;
use Omadonex\Support\Traits\CurlRequestTrait;

class YandexMessageQueueClient implements IMessageQueueClient
{
    use CurlRequestTrait;

    protected $secret;
    protected $key;
    protected $config;

    /**
     * YandexMessageQueueClient constructor.
     * @param $secret
     * @param $key
     * @param array $config
     */
    public function __construct($secret, $key, $config = [])
    {
        $this->secret = $secret;
        $this->key = $key;
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Возвращает конфиг по умолчанию
     * @return array
     */
    protected function getDefaultConfig()
    {
        $host = 'message-queue.api.cloud.yandex.net';

        return [
            'region' => 'ru-central1',
            'service' => 'sqs',
            'host' => $host,
            'endpoint' => "https://{$host}",
            'version' => '2012-11-05',
        ];
    }

    /**
     * Формирует базовую строку запроса для действия
     * @param $queue
     * @param $action
     * @return string
     */
    protected function getDefaultBody($queue, $action)
    {
        return "Action={$action}&Version={$this->config['version']}&QueueUrl=" . urldecode($queue);
    }

    /**
     * Получает сообщение из очереди
     * @param $queue
     * @param bool $autoDelete
     * @return mixed|null
     * @throws \Omadonex\Support\Classes\Exceptions\OmxCurlRequestException
     */
    public function getMessage($queue, $autoDelete = true)
    {
        $body = $this->getDefaultBody($queue, 'ReceiveMessage') . '&WaitTimeSeconds=20';

        $message = null;
        $curlResponse = $this->sendCurlRequest($this->config['endpoint'], $body, $this->getHeaders($body));
        $response = json_decode(json_encode(simplexml_load_string($curlResponse)));

        if (!empty($response->ReceiveMessageResult && !empty($response->ReceiveMessageResult->Message))) {
            $message = json_decode($response->ReceiveMessageResult->Message->Body);
            if ($autoDelete) {
                $this->deleteMessage($queue, $response->ReceiveMessageResult->Message->ReceiptHandle);
            }
        }

        return $message;
    }

    /**
     * Удаляет сообщение из очереди
     * @param $queue
     * @param $receiptHandle
     * @return bool|mixed|string
     * @throws \Omadonex\Support\Classes\Exceptions\OmxCurlRequestException
     */
    public function deleteMessage($queue, $receiptHandle)
    {
        $body = $this->getDefaultBody($queue, 'DeleteMessage') . "&ReceiptHandle={$receiptHandle}";

        return $this->sendCurlRequest($this->config['endpoint'], $body, $this->getHeaders($body));
    }

    /**
     * Генерация подписи
     * @param $body
     * @param $ldt
     * @return string
     */
    private function generateSign($body, $ldt)
    {
        $sdt = substr($ldt, 0, 8);
        $method = 'POST';

        $canonicalUri = '/';
        $canonicalQuerystring = '';
        $canonicalHeaders = "host:{$this->config['host']}\nx-amz-date:{$ldt}\n";
        $signedHeaders = 'host;x-amz-date';

        $payload = hash('sha256', $body);
        $canonicalRequest = "{$method}\n{$canonicalUri}\n{$canonicalQuerystring}\n{$canonicalHeaders}\n{$signedHeaders}\n{$payload}";
        $credentialScope = "$sdt/{$this->config['region']}/{$this->config['service']}/aws4_request";
        $hash = hash('sha256', $canonicalRequest);
        $stringToSign = "AWS4-HMAC-SHA256\n{$ldt}\n{$credentialScope}\n{$hash}";
        $signingKey = $this->getSignatureKey($sdt);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return "AWS4-HMAC-SHA256 Credential={$this->key}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
    }

    /**
     * Генерация ключа подписи
     * @param $dateStamp
     * @return string
     */
    private function getSignatureKey($dateStamp)
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4'.$this->secret, true);
        $kRegion = hash_hmac('sha256', $this->config['region'], $kDate, true);
        $kService = hash_hmac('sha256', $this->config['service'], $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        return $kSigning;
    }

    /**
     * Формирует заголовки запроса
     * @param $body
     * @return array
     */
    private function getHeaders($body)
    {
        $ldt = gmdate('Ymd\THis\Z');

        return [
            'Content-Type: application/x-www-form-urlencoded',
            "X-Amz-Date:{$ldt}",
            'Authorization: ' . $this->generateSign($body, $ldt),
            "Host:{$this->config['host']}",
        ];
    }
}