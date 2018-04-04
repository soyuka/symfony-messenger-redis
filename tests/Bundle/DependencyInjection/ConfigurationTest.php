<?php

namespace Soyuka\RedisMessengerAdapter\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Soyuka\RedisMessengerAdapter\Tests\Fixtures\Message;

class ConfigurationTest extends TestCase
{
    private $configuration;
    private $processor;

    public function setUp()
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig()
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, array('redis_messenger' => array('messages' => array(Message::class => 'queue'))));

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);

        $this->assertEquals(array(
            'enabled' => true,
            'messages' => array(Message::class => array('queue' => 'queue', 'ttl' => 10000, 'blockingTimeout' => 1000)),
            'redis' => array('url' => '127.0.0.1', 'port' => 6379, 'serializer' => \Redis::SERIALIZER_PHP),
        ), $config);
    }
}
