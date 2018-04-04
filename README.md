Redis adapter for symfony/messenger
===========================

[![Build Status](https://travis-ci.org/soyuka/symfony-messenger-redis.svg?branch=master)](https://travis-ci.org/soyuka/symfony-messenger-redis)

This is an experimental Receiver/Sender on Redis for the symfony/messenger component.

## Quick start

For now we're exposing a bundle which is pre-configuring the Messenger component with receivers and senders.

```console
composer require symfony/messenger soyuka/symfony-messenger-redis
```

Add the bundle `new Soyuka\RedisMessengerAdapter\Bundle\RedisMessengerAdapterBundle()`.

Also requires the redis extension.

Add the following configuration:

```yaml
redis_messenger_adapter:
    messages:
        'App\Message\Foo': 'foo_queue'
```

Add a message handler:

```php
<?php

namespace App\MessageHandler;

use App\Message\Foo;

final class FooHandler
{
    public function __invoke(Foo $message)
    {
    }
}
```

Tag it:

```yaml
services:
  App\MessageHandler\FooHandler:
      tags:
          - { name: messenger.message_handler }
```

You're done!

Launch `bin/console messenger:consume-messages redis_messenger.receiver.foo_queue` and dispatch messages from the bus:

```php
<?php
$bus->dispatch(new Foo());
```

## Configuration reference

```yaml
redis_messenger_adapter:
    redis:
        url: '127.0.0.1'
        port: 6379
        serializer: !php/const \Redis::SERIALIZER_IGBINARY # default is \Redis::SERIALIZER_PHP
    messages:
        'App\Message\Foo': 'foo_queue'
        'App\Message\Bar':
            queue: 'bar_queue'
            ttl: 10000
            blockingTimeout: 1000
```

## Internals

Relevant discussion: https://twitter.com/jderusse/status/980768426116485122

The sender uses a List and uses `RPUSH` (add value to the tail of the list).
The receiver uses `BRPOPLPUSH` which reads the last element of the list and adds in to the head of another list (`queue_processing`). If no elements are present it'll block the connection until a new element shows up or the timeout is reached. When timeout is reached it works like a "ping" of some sort (todo wait for 26632 to be merged and `$handle(null)`).
On every iteration, we will check the `queue_processing` list. For every items in this queue we have a corresponding `key` in redis with a given `ttl`. If the `key` has expired, the item is `LREM` (removed) from `queue_processing` and put back in the origin queue to be processed again.
This workaround helps avoiding lost messages.

I started a RedisAdapter and may add it to symfony once messenger documentation and AMQP adapter are merged.

- https://github.com/symfony/symfony/pull/26632 (AMQP adapter PR)
- https://github.com/symfony/symfony-docs/pull/9437 (messenger documentation)
- https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Messenger (messenger component code)
