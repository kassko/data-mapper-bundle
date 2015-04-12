<?php

namespace Kassko\Bundle\DataMapperBundle\ExpressionLanguage;

use Kassko\DataMapper\Expression\ExpressionFunction;
use Kassko\DataMapper\Expression\ExpressionFunctionProviderInterface;

/**
* ExpressionFunctionProvider
*
* @author kko
*/
class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'service',
                function ($arg) {
                    return sprintf('$this->get(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['container']->get($value);
                }
            ),
            new ExpressionFunction(
                'parameter',
                function ($arg) {
                    return sprintf('$this->getParameter(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['container']->getParameter($value);
                }
            )
        ];
    }
}
