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
redis_messenger:
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
redis_messenger:
    redis:
        url: '127.0.0.1'
        port: 6379
        serializer: !php/const \Redis::SERIALIZER_IGBINARY # default is \Redis::SERIALIZER_PHP
    messages:
        'App\Message\Foo': 'foo_queue'
        'App\Message\Bar': 'bar_queue'
```

## Internals

The sender uses a List and uses `RPUSH` (append value to list).
The receiver uses `BLPOP` which reads the first element of the list. If no elements are present it'll block the connection until a new element shows up.

I started a RedisAdapter and may add it to symfony once messenger documentation and AMQP adapter are merged.

- https://github.com/symfony/symfony/pull/26632 (AMQP adapter PR)
- https://github.com/symfony/symfony-docs/pull/9437 (messenger documentation)
- https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Messenger (messenger component code)

Although, for redis if we want to use only one queue we can't simply use RPUSH/BLPOP anymore. Indeed this works here because 1 sender works with 1 receiver. If we use 2 receiver on the same queue they may take messages that aren't theirs. Anyway, to guarantee proper message delivery I think it's best to use 1 queue per message. At least it's the reasoning behind this bundle and why I introduced a new configuration reference.
