<?php

namespace Kassko\Bundle\DataAccessBundle;

use Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler\AddListenersToResolvePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KasskoDataAccessBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddListenersToResolvePass());
    }
}
