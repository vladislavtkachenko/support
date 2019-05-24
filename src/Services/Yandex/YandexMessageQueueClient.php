<?php

namespace App\Services;

use Aws\Sqs\SqsClient;
use Omadonex\Support\Interfaces\IMessageQueueClient;

class YandexMessageQueueClient implements IMessageQueueClient
{
    /**
     * @var SqsClient
     */
    private $client;

    /**
     * YandexMessageQueueClient constructor.
     * @param $key
     * @param $secret
     * @param array $config
     */
    public function __construct($key, $secret, $config = [])
    {
        $this->client = new SqsClient(array_merge($this->getConfig($key, $secret), $config));
    }

    /**
     * Возвращает конфиг по умолчанию
     * @return array
     */
    protected function getConfig($key, $secret)
    {
        return [
            'region' => 'ru-central1',
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
            'version' => '2012-11-05',
        ];
    }

    /**
     * @param $queue
     * @param $message
     * @param int $delaySeconds
     * @param null $attributes
     * @return mixed
     */
    public function sendMessage($queue, $message, $delaySeconds = 20, $attributes = null)
    {
        $params = [
            'QueueUrl' => $queue,
            'MessageBody' => $message ,
        ];

        if ($delaySeconds) {
            $params['DelaySeconds'] = $delaySeconds;
        }

        if ($attributes) {
            $params['MessageAttributes'] = $attributes;
        }

        return $this->client->sendMessage($params);
    }

    /**
     * @param $queue
     * @param int $waitTimeSeconds
     * @return mixed
     */
    public function receiveMessage($queue, $waitTimeSeconds = 20)
    {
        $params = [
            'QueueUrl' => $queue,
            'WaitTimeSeconds' => $waitTimeSeconds,
        ];

        return $this->client->receiveMessage($params);
    }

    /**
     * @param $queue
     * @param $receiptHandle
     * @return mixed
     */
    public function deleteMessage($queue, $receiptHandle)
    {
        $params = [
            'QueueUrl' => $queue,
            'ReceiptHandle' => $receiptHandle,
        ];

        return $this->client->deleteMessage($params);
    }
}