data-access-bundle
==================

This bundle integrates data-access to Symfony.
Please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/alpha/README.md).

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
