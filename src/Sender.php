<?php

namespace Soyuka\RedisMessengerAdapter;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;

class Sender implements SenderInterface
{
    private $encoder;
    private $connection;
    private $queue;

    public function __construct(EncoderInterface $encoder, Connection $connection, string $queue)
    {
        $this->encoder = $encoder;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope)
    {
        $this->connection->add($this->queue, $this->encoder->encode($envelope));
    }
}
