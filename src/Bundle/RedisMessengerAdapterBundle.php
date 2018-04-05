<?php

namespace Soyuka\RedisMessengerAdapter\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Soyuka\RedisMessengerAdapter\Bundle\DependencyInjection\Compiler\RedisAdapterPass;

class RedisMessengerAdapterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RedisAdapterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
