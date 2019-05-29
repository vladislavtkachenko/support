<?php

namespace Omadonex\Support\Interfaces;

interface ICentrifugoClient
{
    /**
     * Публикует данные на один канал
     * @param $channel
     * @param $data
     * @return mixed
     */
    public function publish($channel, $data);

    /**
     * Вещает данные на несколько каналов
     * @param $channels
     * @param $data
     * @return mixed
     */
    public function broadcast($channels, $data);

    /**
     * Параметры соединения для использования на фронте
     * @param $userId
     * @param bool $sockJs
     * @param array $options
     * @return mixed
     */
    public function getConnectionParams($userId, $sockJs = false, $options = []);

    /**
     * Генерирует токен для использования в js
     * @param array $payload
     * @return mixed
     */
    public function generateToken($payload = []);
}