<?php

declare(strict_types=1);

namespace App\Messenger;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration
{
    public function __invoke()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('messenger');

        $rootNode
            ->children()
                ->arrayNode('messages')
                    ->useAttributeAsKey('message')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['queue' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('queue')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
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
                            ->defaultValue(Redis::SERIALIZER_PHP)
                            ->info('Redis serializer constant, one of: Redis::SERIALIZER_PHP, Redis::SERIALIZER_IGBINARY')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }
}
