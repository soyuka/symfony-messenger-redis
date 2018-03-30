<?php

namespace Soyuka\RedisMessengerAdapter;

class Redis extends \Redis
{
    public function __construct($url = '127.0.0.1', $port = 6379, $serializer = \Redis::SERIALIZER_PHP)
    {
        parent::__construct();
        $this->connect($url, $port);
        $this->setOption(\Redis::OPT_SERIALIZER, $serializer);
        $this->setOption(\Redis::OPT_READ_TIMEOUT, -1);
    }

    // @TODO
    public static function fromDsn(string $dsn): self
    {
        return new self();
    }
}
