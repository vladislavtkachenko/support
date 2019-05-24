<?php

namespace Omadonex\Support\Interfaces;

interface IMessageQueueClient
{
    /**
     * Receives message from queue
     * @param $queue
     * @param bool $autoDelete
     * @return mixed
     */
    public function getMessage($queue, $autoDelete = true);

    /**
     * Deletes message from queue
     * @param $queue
     * @param $receiptHandle
     * @return mixed
     */
    public function deleteMessage($queue, $receiptHandle);
}