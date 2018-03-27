<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;

class Sender implements SenderInterface
{
    private $encoder;
    private $redis;
    private $queue;

    public function __construct(EncoderInterface $encoder, Redis $redis, string $queue)
    {
        $this->encoder = $encoder;
        $this->redis = $redis;
        $this->queue = $queue;
    }

    public function send($message)
    {
        $this->redis->rpush($this->queue, $this->encoder->encode($message));
    }
}
