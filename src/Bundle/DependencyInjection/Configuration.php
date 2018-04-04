<?php

namespace Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection;

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

        $treeBuilder
            ->root('redis_messenger')
                ->canBeDisabled()
                ->children()
                    ->arrayNode('messages')
                        ->useAttributeAsKey('message')
                        ->arrayPrototype()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array('queue' => $v);
                                })
                            ->end()
                            ->children()
                                ->scalarNode('queue')->end()
                                ->scalarNode('ttl')
                                    ->defaultValue(10000)
                                    ->info('This represent how long the value stays in the processing queue before being requeued.')
                                ->end()
                                ->scalarNode('blockingTimeout')
                                    ->defaultValue(1000)
                                    ->info('The bRPopLPush redis timeout.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('redis')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('url')
                                ->defaultValue('127.0.0.1')
                                ->info('Redis url')
                            ->end()
                            ->scalarNode('port')
                                ->defaultValue(6379)
                                ->info('Redis port')
                            ->end()
                            ->scalarNode('serializer')
                                ->defaultValue(\Redis::SERIALIZER_PHP)
                                ->info('Redis serializer constant, one of: Redis::SERIALIZER_PHP, Redis::SERIALIZER_IGBINARY')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
