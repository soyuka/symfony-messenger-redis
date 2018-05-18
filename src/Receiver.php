<?php

namespace Soyuka\RedisMessengerAdapter;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;

class Receiver implements ReceiverInterface
{
    private $decoder;
    private $queue;
    private $connection;
    private $processingTtl;
    private $blockingTimeout;
    private $shouldStop = false;

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
    public function receive(callable $handler): void
    {
        while (!$this->shouldStop) {
            if (null === $message = $this->connection->waitAndGet($this->queue, $this->processingTtl, $this->blockingTimeout)) {
                $handler(null);
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }

                continue;
            }

            try {
                $handler($this->decoder->decode($message));
                $this->connection->ack($this->queue, $message);
            } catch (RejectMessageException $e) {
                $this->connection->reject($this->queue, $message);
            } catch (\Throwable $e) {
                $this->connection->reject($this->queue, $message);
                throw $e;
            } finally {
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            }
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }
}
