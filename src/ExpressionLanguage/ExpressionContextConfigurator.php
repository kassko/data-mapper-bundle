<?php

namespace Kassko\Bundle\DataMapperBundle\ExpressionLanguage;

use Kassko\DataMapper\Expression\ExpressionContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Visits ExpressionLanguage to enhance it with Symfony container feature.
*
* @author kko
*/
class ExpressionContextConfigurator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;  
    }

    public function configure(ExpressionContext $expressionContext)
    {
        $expressionContext['container'] = $this->container;
    }
}
