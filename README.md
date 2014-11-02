data-access-bundle
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-access-bundle/v/stable.png)](https://packagist.org/packages/kassko/data-access-bundle)
[![Total Downloads](https://poser.pugx.org/kassko/data-access-bundle/downloads.png)](https://packagist.org/packages/kassko/data-access-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-access-bundle/v/unstable.png)](https://packagist.org/packages/kassko/data-access-bundle)

This bundle integrates data-access to Symfony.
Please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/master/README.md).
It provides a service named "data_access.result_builder_factory". Use it to create a "ResultBuilderFactory" instance instead of "DataAccessProvider".

Installation on Symfony 2
----------------

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

