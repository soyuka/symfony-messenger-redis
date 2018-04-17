<?php

declare(strict_types=1);

namespace Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Soyuka\RedisMessengerAdapter\Connection;
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
        $definitions = array();
        $redis = new Reference(Connection::class);

        // extracted from registerMessengerConfiguration
        $senderLocatorMapping = array();
        $messageToSenderIdsMapping = array();

        $messages = $container->getParameter(self::PREFIX.'.messages');

        if (!$messages) {
            return;
        }

        foreach ($messages as $class => $message) {
            $senderDefinition = new Definition(Sender::class, array(
                new Reference('messenger.transport.default_encoder'),
                $redis,
                $message['queue'],
            ));
            $senderDefinition->addTag('messenger.sender', array('name' => null));

            $sender = self::PREFIX.'.sender.'.$message['queue'];
            $container->setDefinition($sender, $senderDefinition);
            $senderLocatorMapping[$sender] = new Reference($sender);
            $messageToSenderIdsMapping[$class] = array($sender);
        }

        $container->getDefinition('messenger.sender_locator')->replaceArgument(0, $senderLocatorMapping);
        $container->getDefinition('messenger.asynchronous.routing.sender_locator')->replaceArgument(1, $messageToSenderIdsMapping);

        $receivers = array();
        foreach ($messages as $message) {
            $receiverDefinition = new Definition(Receiver::class, array(
                new Reference('messenger.transport.default_decoder'),
                $redis,
                $message['queue'],
                $message['ttl'],
                $message['blockingTimeout'],
            ));
            $receiverDefinition->addTag('messenger.receiver', array('name' => null));

            $receiver = self::PREFIX.'.receiver.'.$message['queue'];
            $container->setDefinition($receiver, $receiverDefinition);
            $receivers[] = $receiver;
        }

        $container->getDefinition(ListMessengerReceivers::class)->setArgument(0, $receivers);
    }
}
