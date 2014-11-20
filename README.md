data-access-bundle
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-access-bundle/v/stable.png)](https://packagist.org/packages/kassko/data-access-bundle)
[![Total Downloads](https://poser.pugx.org/kassko/data-access-bundle/downloads.png)](https://packagist.org/packages/kassko/data-access-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-access-bundle/v/unstable.png)](https://packagist.org/packages/kassko/data-access-bundle)

This bundle integrates the component data-access into Symfony projects. This component allows to give an object representation of raw data. It gives a lot of flexibility to representate these raw data and allows to make intelligent mapping.

To know more about this component and how to use it, please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/master/README.md).

Installation on Symfony 2
----------------

Add to your composer.json:
```json
"require": {
    "kassko/data-access-bundle": "~0.3.0"
}
```

Register the bundle to the kernel:
```php
public function registerBundles()
{
    $bundles = array(
        new Kassko\Bundle\DataAccessBundle\KasskoDataAccessBundle(),
        new Kassko\Bundle\ClassResolverBundle\KasskoClassResolverBundle(),
    );
}
```

Services
----------
data-access-bundle provides services to facilitate to use data-access components:

###### kassko_data_access.result_builder_factory
It represents a ResultBuilderFactory instance.

###### kassko_data_access.query_factory
It represents a QueryFactory instance.

To know more about this component and how to use it, please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/master/README.md).

Tags (dependency injection tags)
----------
###### kassko_data_access.listener
data-access need to be able to retrieve an object listener from its full qualified class name. To help it, register your object listener as service and tag it.
To know more about object listener, please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/master/README.md).

###### kassko_data_access.mapping_loader
Simplify the work to expose a custom mapping loader. Register it as service and tag it.
To know more about custom mapping loader creation (contract to implement etc.), please read the [data-access documentation reference](https://github.com/kassko/data-access/blob/master/README.md).

###### kassko_data_access.registry_item
Facilitate to add a service in the registry. So tag it.

Configuration
----------
```yaml
kassko_data_access:
    mapping:
        default_resource_type: annotations # Default is "annotations" or other type (1).
        default_resource_dir: # Optional.
        default_provider_method: # Optional.
        bundles: #optional section
            some_bundle
                resource_type: annotations # Default is "annotations" or other type (1).
                resource_dir: # The resource dir of the given bundle.
                provider_method: ~ # Required. Default value is null.
                objects: # Optional section.
                    some_object:
                        resource_type: # Optional.
                        resource_path: # Optional. The resource directory with the resource name. If not defined, data-access fallback to resource_name and prepend to it resource_dir (or default_resource_dir). So if resource_path is not defined, case resource_name and resource_dir (or default_resource_dir) must be defined.
                        resource_name: # Optional. Only the resource name (so without the directory).
                        provider_method: # Optional. Override default_provider_method.
                        object_class: # Required (full qualified object class name).
    logger_service: # Optional. A logger service name. Il will be used for logging in data-access component.
    cache:
        metadata_cache: # Optional section
            class: Optional.
            id: # Optional.
            life_time: Default is 0
            is_shared: Default is false
            adapter_class: # Default is "Kassko\Bundle\DataAccessBundle\Adapter\Cache\DoctrineCacheAdapter"
        result_cache: # Optional section and same as metadata_cache
```
(1) availables types are annotations, yaml, php, php_file, yaml_file.
And maybe others if you add custom mapping loader.
