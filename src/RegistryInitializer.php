<?php

namespace Kassko\Bundle\DataAccessBundle;

use Kassko\DataAccess\Registry\Registry;

/**
 * Command (from Command pattern, not Symfony command) witch initialize the registry.
 * The contract is to be callable (so in our case, having a __invoke() method).
 *
 * @author kko
 */
class RegistryInitializer
{
    private $items = [];

    public function __invoke()
    {
        $registry = Registry::getInstance();
        foreach ($this->items as $key => $value) {
            $registry[$key] = $value;
        }
    }

    public function addItem($key, $value)
    {
        $this->items[$key] = $value;
    }
}
