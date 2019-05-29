<?php

namespace Omadonex\Support\Interfaces;

interface IMessageQueueClient
{
    /**
     * Помещает сообщение в очередь
     * @param $queue
     * @param $message
     * @param int $delaySeconds
     * @param null $attributes
     * @return mixed
     */
    public function sendMessage($queue, $message, $delaySeconds = 0, $attributes = null);

    /**
     * Получает одно или несколько сообщений
     * @param $queue
     * @param bool $rawData
     * @param bool $autoDelete
     * @param int $waitTimeSeconds
     * @return array|mixed|null
     */
    public function receiveMessage($queue, $rawData = false, $autoDelete = false, $waitTimeSeconds = 20);

    /**
     * Удаляет сообщение
     * @param $queue
     * @param $receiptHandle
     * @return mixed
     */
    public function deleteMessage($queue, $receiptHandle);
}