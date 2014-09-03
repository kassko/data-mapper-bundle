data-access-bundle
==================

Installation on Symfony 2
---------------

Add to your composer.json:
```json
"require": {
    "kassko/data-access-bundle": "dev-master"
}
```

Register the bundle to the kernel:
```php
public function registerBundles()
{
    $bundles = array(
    //...
    new Kassko\Bundle\DataAccessBundle\KasskoDataAccessBundle(),
    new Kassko\Bundle\ClassResolverBundle\KasskoClassResolverBundle(),
    //...
    );

    //...
}
```

