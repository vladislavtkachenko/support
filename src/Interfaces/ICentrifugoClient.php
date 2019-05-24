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
}