<?php

namespace Soyuka\RedisMessengerAdapter\Tests\Fixtures;

final class Message
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}
