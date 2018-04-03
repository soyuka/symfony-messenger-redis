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
                echo sprintf('before yield %s', $message['body']).PHP_EOL;
                yield $this->decoder->decode($message);
                echo sprintf('ack %s', $message['body']).PHP_EOL;
                $this->connection->ack($this->queue, $message);
            } catch (RejectMessageException $e) {
                $this->connection->reject($this->queue, $message);
            } catch (\Throwable $e) {
                $this->connection->reject($this->queue, $message);
            }
        }
    }
}
