<?php


use PHPUnit\Framework\TestCase;
use Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\RedisMessengerAdapterExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Soyuka\RedisMessengerAdapter\Connection;
use Soyuka\RedisMessengerAdapter\Command\ListMessengerReceivers;

class RedisMessengerAdapterExtensionTest extends TestCase
{
    private $extension;

    public function setUp()
    {
        $this->extension = new RedisMessengerAdapterExtension();
    }

    public function testConstruct()
    {
        $this->extension = new RedisMessengerAdapterExtension();
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $this->extension);
    }

    public function testLoad()
    {
        $config = array('redis_messenger' => array('messages' => array(Message::class => 'queue')));

        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->setParameter('redis_messenger.messages', array(Message::class => array('queue' => 'queue')))->shouldBeCalled();
        $self = $this;
        $containerBuilderProphecy->setDefinitions(Argument::type('array'))->will(function ($args) use ($self) {
            $self->assertEquals(array_keys($args[0]), array(Connection::class, ListMessengerReceivers::class));
        })->shouldBeCalled();

        $this->extension->load($config, $containerBuilderProphecy->reveal());
    }
}
