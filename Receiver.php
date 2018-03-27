<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;

class Receiver implements ReceiverInterface
{
    private $decoder;
    private $queue;
    private $redis;

    public function __construct(DecoderInterface $decoder, Redis $redis, string $queue)
    {
        $this->decoder = $decoder;
        $this->queue = $queue;
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): iterable
    {
        while (true) {
            $value = $this->redis->blPop($this->queue, 0);

            yield $this->decoder->decode($value[1]);
        }
    }

}
