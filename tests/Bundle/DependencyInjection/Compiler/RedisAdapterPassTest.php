<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Soyuka\RedisMessengerAdapter\Tests\Fixtures\Message;
use Soyuka\RedisMessengerAdapter\Command\ListMessengerReceivers;
use Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\Compiler\RedisAdapterPass;
use Prophecy\Argument;

class RedisAdapterPassTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(CompilerPassInterface::class, new RedisAdapterPass());
    }

    public function testProcess()
    {
        $senderLocatorProphecy = $this->prophesize(Definition::class);
        $senderLocatorProphecy->replaceArgument(0, Argument::type('array'))->shouldBeCalled();
        $asyncSenderLocatorProphecy = $this->prophesize(Definition::class);
        $asyncSenderLocatorProphecy->replaceArgument(1, Argument::type('array'))->shouldBeCalled();
        $commandProphecy = $this->prophesize(Definition::class);
        $commandProphecy->replaceArgument(0, Argument::type('array'))->shouldBeCalled();

        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->getParameter('redis_messenger.messages')->shouldBeCalled()->willReturn(array(Message::class => array('queue' => 'test', 'ttl' => 1000, 'blockingTimeout' => 1000)));
        $containerBuilderProphecy->setDefinition('redis_messenger.sender.test', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->getDefinition('messenger.sender_locator')->shouldBeCalled()->willReturn($senderLocatorProphecy->reveal());
        $containerBuilderProphecy->getDefinition('messenger.asynchronous.routing.sender_locator')->shouldBeCalled()->willReturn($asyncSenderLocatorProphecy->reveal());

        $containerBuilderProphecy->setDefinition('redis_messenger.receiver.test', Argument::type(Definition::class))->shouldBeCalled();

        $containerBuilderProphecy->getDefinition(ListMessengerReceivers::class)->shouldBeCalled()->willReturn($commandProphecy);

        (new RedisAdapterPass())->process($containerBuilderProphecy->reveal());
    }
}
