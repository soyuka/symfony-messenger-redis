<?php

namespace Soyuka\RedisMessengerAdapter\Tests;

require __DIR__.'/../vendor/autoload.php';

use Soyuka\RedisMessengerAdapter\Tests\Fixtures\Message;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Asynchronous\Routing\SenderLocator;
use Symfony\Component\DependencyInjection\Container;
use Soyuka\RedisMessengerAdapter\Redis;
use Symfony\Component\Messenger\Transport\Serialization\Serializer as MessageSerializer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Soyuka\RedisMessengerAdapter\Sender;
use Soyuka\RedisMessengerAdapter\Receiver;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\HandlerLocator;

// Build a serializer
$encoders = array(new JsonEncoder());
$normalizers = array(new ObjectNormalizer());
$serializer = new Serializer($normalizers, $encoders);

// Messenger encoder/decoder
$messageSerializer = new MessageSerializer($serializer);

$queueName = 'test';
$data = 'Hello world!';

// This comes from the Soyuka\RedisMessengerAdapter
$redis = new Redis();
$senderId = 'redis_messenger.senders.test';
$sender = new Sender($messageSerializer, $redis, $queueName);
$receiver = new Receiver($messageSerializer, $redis, $queueName);

$container = new Container();
$container->set($senderId, $sender);

$handler = function ($t) use ($data) {
    if ($t->foo !== $data) {
        exit(1);
    }

    exit(0);
};

$bus = new MessageBus(array(
    new SendMessageMiddleware(new SenderLocator($container, array(
        Message::class => array($senderId),
    ))),
    new HandleMessageMiddleware(new HandlerLocator(array(
        Message::class => $handler,
    ))),
));

$bus->dispatch(new Message($data));

$worker = new Worker($receiver, $bus);
$worker->run();
