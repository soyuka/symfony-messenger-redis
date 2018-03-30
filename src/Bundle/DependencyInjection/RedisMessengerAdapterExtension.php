<?php

namespace Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection;

use Soyuka\RedisMessengerAdapter\Command\ListMessengerReceivers;
use Soyuka\RedisMessengerAdapter\Redis;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class RedisMessengerAdapterExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('redis_messenger.messages', $config['messages']);

        $redisDefinition = new Definition(Redis::class, array(
            $config['redis']['url'],
            $config['redis']['port'],
            $config['redis']['serializer'],
        ));

        $commandDefinition = new Definition(ListMessengerReceivers::class);
        $commandDefinition->addTag('console.command');

        $container->setDefinitions(array(
            Redis::class => $redisDefinition,
            ListMessengerReceivers::class => $commandDefinition,
        ));
    }
}
