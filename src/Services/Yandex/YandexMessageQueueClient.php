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
     * @param $cloudSA_key (ключ сервисного аккаунта в облаке)
     * @param $cloudSA_secret (секрет сервисного аккаунта в облаке)
     * @param array $config
     */
    public function __construct($cloudSA_key, $cloudSA_secret, $config = [])
    {
        $this->client = new SqsClient(array_merge($this->getConfig($cloudSA_key, $cloudSA_secret), $config));
    }

    /**
     * Конфиг по умолчанию
     * @param $cloudSA_key
     * @param $cloudSA_secret
     * @return array
     */
    protected function getConfig($cloudSA_key, $cloudSA_secret)
    {
        return [
            'region' => 'ru-central1',
            'credentials' => [
                'key' => $cloudSA_key,
                'secret' => $cloudSA_secret,
            ],
            'version' => '2012-11-05',
        ];
    }

    /**
     * Помещает сообщение в очередь
     * @param $queue
     * @param $message
     * @param int $delaySeconds
     * @param null $attributes
     * @return mixed
     */
    public function sendMessage($queue, $message, $delaySeconds = 0, $attributes = null)
    {
        $params = [
            'QueueUrl' => $queue,
            'MessageBody' => $message ,
        ];

        $params['DelaySeconds'] = $delaySeconds;

        if ($attributes) {
            $params['MessageAttributes'] = $attributes;
        }

        return $this->client->sendMessage($params);
    }

    /**
     * Получает одно или несколько сообщений
     * @param $queue
     * @param bool $rawData
     * @param bool $autoDelete
     * @param int $waitTimeSeconds
     * @return array|mixed|null
     */
    public function receiveMessage($queue, $rawData = false, $autoDelete = false, $waitTimeSeconds = 20)
    {
        $params = [
            'QueueUrl' => $queue,
            'WaitTimeSeconds' => $waitTimeSeconds,
        ];

        $result = $this->client->receiveMessage($params);
        if ($result->hasKey('Messages')) {
            $messages = $result->get('Messages');
            if (is_array($messages) && !empty($messages)) {
                $data = [];
                foreach ($messages as $message) {
                    $messageData = [
                        'handle' => $message['ReceiptHandle'],
                        'body' => json_decode($message['Body']),
                    ];
                    if ($rawData) {
                        $messageData = array_merge($messageData, ['raw' => $message]);
                    }
                    $data[] = $messageData;

                    if ($autoDelete) {
                        $this->deleteMessage($queue, $messageData['ReceiptHandle']);
                    }
                }

                return $data;
            }
        }

        return null;
    }

    /**
     * Удаляет сообщение
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