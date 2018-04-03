<?php

namespace Soyuka\RedisMessengerAdapter;

use Symfony\Component\Messenger\Adapter\Factory\AdapterInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;

class RedisAdapter implements AdapterInterface
{
    private $encoder;
    private $decoder;
    private $connection;

    public function __construct(EncoderInterface $encoder, DecoderInterface $decoder, Connection $connection, string $queue)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->connection = $connection;
    }

    public function receiver(): ReceiverInterface
    {
        return new Receiver($this->decoder, $this->connection, $this->queue);
    }

    public function sender(): SenderInterface
    {
        return new Sender($this->encoder, $this->connection, $this->queue);
    }
}
