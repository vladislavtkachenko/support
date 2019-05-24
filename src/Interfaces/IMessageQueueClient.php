<?php

namespace Omadonex\Support\Interfaces;

interface IMessageQueueClient
{
    /**
     * @param $queue
     * @param $message
     * @param int $delaySeconds
     * @param null $attributes
     * @return mixed
     */
    public function sendMessage($queue, $message, $delaySeconds = 20, $attributes = null);

    /**
     * @param $queue
     * @param int $waitTimeSeconds
     * @return mixed
     */
    public function receiveMessage($queue, $waitTimeSeconds = 20);

    /**
     * @param $queue
     * @param $receiptHandle
     * @return mixed
     */
    public function deleteMessage($queue, $receiptHandle);
}