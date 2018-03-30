Redis adapter for symfony/messenger
===========================

Requirements:

- symfony/messenger (experimental)
- Redis
- IgBinary to use Redis::SERIALIZER_IGBINARY

The sender:

        $this->redis->rpush($this->queue, $this->encoder->encode($message));

The receiver (uses `blPop`, blocks the connection while waiting for new data):

            $value = $this->redis->blPop($this->queue, 0);

Configuration:

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

