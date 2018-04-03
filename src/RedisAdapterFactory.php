<?php

namespace Soyuka\RedisMessengerAdapter;

use Symfony\Component\Messenger\Adapter\Factory\AdapterInterface;
use Symfony\Component\Messenger\Adapter\Factory\AdapterFactoryInterface;

class RedisAdapterFactory implements AdapterFactoryInterface
{
    private $encoder;
    private $decoder;
    private $queue;

    public function __construct(EncoderInterface $encoder, DecoderInterface $decoder, string $queue)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->queue = $queue;
    }

    public function create(string $dsn): AdapterInterface
    {
        return new RedisAdapter($this->encoder, $this->decoder, Connection::fromDsn($dsn), $this->queue);
    }

    public function supports(string $dsn): bool
    {
        return 0 === strpos($dsn, 'redis://');
    }
}
