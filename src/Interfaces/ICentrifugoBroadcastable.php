<?php

namespace Omadonex\Support\Interfaces;

interface ICentrifugoBroadcastable
{
    /**
     * @return mixed
     */
    public function toCentrifugoJson();
}