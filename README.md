Redis adapter for messenger
===========================

/!\ Not installable through composer yet, could be a small bundle though /!\

Requirements:

- symfony/messenger (experimental)
- Redis
- IgBinary to use Redis::SERIALIZER_IGBINARY

The sender:

```php
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
```

The receiver (uses `blPop`, blocks the connection while waiting for new data):

```php
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

```

Configuration:

```yaml
prefix:
    messenger:
        redis:
            url: '127.0.0.1'
            port: 6379
            serializer: !php/const \Redis::SERIALIZER_IGBINARY
        messages:
            'App\Message\Foo': 'foo_queue'
            'App\Message\Bar': 'bar_queue'
```

Add your `messenger.message_handler` tagged services (handlers).

Missing:

- Extension + kernel that loads the CompilerPass with a higher priority then the default MessengerPass:

```php
<?php
        $container->addCompilerPass(new MessagePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
```

```php
<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as DIExtension;

class Extension extends DIExtension
{
    public function getAlias()
    {
        return 'app';
    }

    public function getNamespace()
    {
        return false;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('app.messenger.redis.url', $config['messenger']['redis']['url']);
        $container->setParameter('app.messenger.redis.port', $config['messenger']['redis']['port']);
        $container->setParameter('app.messenger.redis.serializer', $config['messenger']['redis']['serializer']);
        $container->setParameter('app.messenger.messages', $config['messenger']['messages']);
    }
}
```

- Configuration load:

```php
<?php

declare(strict_types=1);

namespace Bender;

use App\Messenger\Configuration as MessengerConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->append((new MessengerConfiguration())())
            ->end();

        return $treeBuilder;
    }
}

```
