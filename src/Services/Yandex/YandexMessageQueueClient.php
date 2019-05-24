<?php

namespace Omadonex\Support\Services\Yandex;

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
     * @param bool $autoDelete
     * @param bool $returnBodyObj
     * @param int $waitTimeSeconds
     * @return mixed|null
     */
    public function receiveMessage($queue, $autoDelete = true, $returnBodyObj = true, $waitTimeSeconds = 20)
    {
        $params = [
            'QueueUrl' => $queue,
            'WaitTimeSeconds' => $waitTimeSeconds,
        ];

        $result = $this->client->receiveMessage($params);
        if ($result->hasKey('Messages')) {
            $messages = $result->get('Messages');
            if (is_array($messages) && !empty($messages)) {
                $messageData = $messages[0];
                if ($autoDelete) {
                    $this->deleteMessage($queue, $messageData['ReceiptHandle']);
                }

                if ($returnBodyObj) {
                    return json_decode($messageData['Body']);
                }

                return $messageData;
            }
        }

        return null;
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