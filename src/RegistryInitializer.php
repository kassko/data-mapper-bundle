<?php

namespace Kassko\Bundle\DataMapperBundle;

use Kassko\DataMapper\Registry\Registry;

/**
 * Command (from Command pattern, not Symfony command) witch initialize the registry.
 * The contract is to be callable (so in our case, having a __invoke() method).
 *
 * @author kko
 */
class RegistryInitializer
{
    private $items = [];

    public function supply()
    {
        $registry = Registry::getInstance();
        foreach ($this->items as $key => $value) {
            $registry[$key] = $value;
        }
    }

    public function flush()
    {
        Registry::getInstance()->flush();
    }

    public function addItem($key, $value)
    {
        $this->items[$key] = $value;
    }
}
