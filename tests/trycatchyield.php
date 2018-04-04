<?php
/**
 * Weird bug:.
 *
 * @sroze reproduced: https://gist.github.com/sroze/2ed22483b5fd35081b4fa31f02fd1a24
 * It's not a bug: https://bugs.php.net/bug.php?id=76181
 * Fix: https://3v4l.org/nWpTG (use `yield` in the catch)
 */
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Asynchronous\Routing\SenderLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Transport\Serialization\Serializer as MessageSerializer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\HandlerLocator;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;

// Build a serializer
$encoders = array(new JsonEncoder());
$normalizers = array(new ObjectNormalizer());
$serializer = new Serializer($normalizers, $encoders);
// Messenger encoder/decoder
$messageSerializer = new MessageSerializer($serializer);

class Message
{
    public $str;

    public function __construct($str)
    {
        $this->str = $str;
    }
}

class Sender implements SenderInterface
{
    private $encoder;
    public $list = array();

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function send($message)
    {
        $this->list[] = $this->encoder->encode($message);
    }
}

class Receiver implements ReceiverInterface
{
    private $decoder;
    private $sender;

    public function __construct(DecoderInterface $decoder, SenderInterface $sender)
    {
        $this->decoder = $decoder;
        $this->sender = $sender;
    }

    public function receive(): iterable
    {
        while (true) {
            if (null === $message = array_shift($this->sender->list)) {
                continue;
            }

            try {
                yield $this->decoder->decode($message);
            } catch (\Throwable $e) {
                echo 'Got exception do nothing'.PHP_EOL;
            }
        }
    }
}

$sender = new Sender($messageSerializer);
$receiver = new Receiver($messageSerializer, $sender);

$container = new Container();
$container->set('sender', $sender);

$throwed = false;

$handler = function ($t) use (&$throwed) {
    if (false === $throwed) {
        $throwed = true;
        throw new \Exception('Fail');
    }

    echo sprintf('Got message %s', $t->str).PHP_EOL;
};

$bus = new MessageBus(array(
    new SendMessageMiddleware(new SenderLocator($container, array(
        Message::class => array('sender'),
    ))),
    new HandleMessageMiddleware(new HandlerLocator(array(
        Message::class => $handler,
    ))),
));

$bus->dispatch(new Message('test1'));
$bus->dispatch(new Message('test2'));
$bus->dispatch(new Message('test3'));

$worker = new Worker($receiver, $bus);
$worker->run();

// Expected output:
// Got exception do nothing
// Got message test2
// Got message test3

// Actual output:
// Got exception do nothing
// Got message test3
