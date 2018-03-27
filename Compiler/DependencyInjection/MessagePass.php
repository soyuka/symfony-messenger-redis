<?php

declare(strict_types=1);

namespace App\Messenger\Compiler\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use App\Messenger\Redis;
use App\Messenger\Receiver;
use App\Messenger\Sender;
use App\Messenger\Command\ListMessengerReceivers;

/**
 * Adds Message senders/receivers
 */
final class MessagePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $queues = [];
        $definitions = [];
        $redis = new Reference(Redis::class);

        // extracted from registerMessengerConfiguration
        $senderLocatorMapping = [];
        $messageToSenderIdsMapping = [];

        foreach ($container->getParameter('app.messenger.messages') as $class => $message) {
            if (!in_array($message['queue'], $queues, true)) {
                $queues[] = $message['queue'];
            }

            $senderDefinition = new Definition(Sender::class, array(
                new Reference('messenger.transport.default_encoder'),
                $redis,
                $message['queue'],
            ));
            $senderDefinition->addTag('messenger.sender');

            $sender = 'app_messenger.sender.'.$message['queue'];
            $container->setDefinition($sender, $senderDefinition);
            $senderLocatorMapping[$sender] = new Reference($sender);
            $messageToSenderIdsMapping[$class] = [$sender];
        }

        $container->getDefinition('messenger.sender_locator')->replaceArgument(0, $senderLocatorMapping);
        $container->getDefinition('messenger.asynchronous.routing.sender_locator')->replaceArgument(1, $messageToSenderIdsMapping);

        $receivers = [];
        foreach ($queues as $queue) {
            $receiverDefinition = new Definition(Receiver::class, array(
                new Reference('messenger.transport.default_decoder'),
                $redis,
                $queue,
            ));
            $receiverDefinition->addTag('messenger.receiver');

            $receiver = 'app_messenger.receiver.'.$queue;
            $container->setDefinition($receiver, $receiverDefinition);
            $receivers[] = $receiver;
        }

        $container->getDefinition(ListMessengerReceivers::class)->replaceArgument(0, $receivers);
    }
}
