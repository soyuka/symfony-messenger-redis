<?php

declare(strict_types=1);

namespace Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Soyuka\RedisMessengerAdapter\Redis;
use Soyuka\RedisMessengerAdapter\Receiver;
use Soyuka\RedisMessengerAdapter\Sender;
use Soyuka\RedisMessengerAdapter\Command\ListMessengerReceivers;

/**
 * Adds Message senders/receivers.
 */
final class RedisAdapterPass implements CompilerPassInterface
{
    const PREFIX = 'redis_messenger';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $queues = array();
        $definitions = array();
        $redis = new Reference(Redis::class);

        // extracted from registerMessengerConfiguration
        $senderLocatorMapping = array();
        $messageToSenderIdsMapping = array();

        foreach ($container->getParameter(self::PREFIX.'.messages') as $class => $message) {
            if (!isset($queues[$message['queue']])) {
                $queues[$message['queue']] = true;
            }

            $senderDefinition = new Definition(Sender::class, array(
                new Reference('messenger.transport.default_encoder'),
                $redis,
                $message['queue'],
            ));
            $senderDefinition->addTag('messenger.sender');

            $sender = self::PREFIX.'.sender.'.$message['queue'];
            $container->setDefinition($sender, $senderDefinition);
            $senderLocatorMapping[$sender] = new Reference($sender);
            $messageToSenderIdsMapping[$class] = array($sender);
        }

        $container->getDefinition('messenger.sender_locator')->replaceArgument(0, $senderLocatorMapping);
        $container->getDefinition('messenger.asynchronous.routing.sender_locator')->replaceArgument(1, $messageToSenderIdsMapping);

        $receivers = array();
        foreach ($queues as $queue => $noop) {
            $receiverDefinition = new Definition(Receiver::class, array(
                new Reference('messenger.transport.default_decoder'),
                $redis,
                $queue,
            ));
            $receiverDefinition->addTag('messenger.receiver');

            $receiver = self::PREFIX.'.receiver.'.$queue;
            $container->setDefinition($receiver, $receiverDefinition);
            $receivers[] = $receiver;
        }

        $container->getDefinition(ListMessengerReceivers::class)->replaceArgument(0, $receivers);
    }
}
