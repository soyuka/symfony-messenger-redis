<?php

namespace Soyuka\RedisMessengerAdapter;

use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;

class Receiver implements ReceiverInterface
{
    private $decoder;
    private $queue;
    private $connection;
    private $processingTtl;
    private $blockingTimeout;

    public function __construct(DecoderInterface $decoder, Connection $connection, string $queue, int $processingTtl = 10000, int $blockingTimeout = 1000)
    {
        $this->decoder = $decoder;
        $this->queue = $queue;
        $this->connection = $connection;
        $this->processingTtl = $processingTtl;
        $this->blockingTimeout = $blockingTimeout;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): iterable
    {
        while (true) {
            if (null === $message = $this->connection->waitAndGet($this->queue, $this->processingTtl, $this->blockingTimeout)) {
                continue;
            }

            try {
                yield $this->decoder->decode($message);
                $this->connection->ack($this->queue, $message);
            } catch (RejectMessageException $e) {
                yield
                $this->connection->reject($this->queue, $message);
            } catch (\Throwable $e) {
                yield
                $this->connection->reject($this->queue, $message);
            }
        }
    }
}
