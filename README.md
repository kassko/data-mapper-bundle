data-mapper-bundle
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-mapper-bundle/v/stable.png)](https://packagist.org/packages/kassko/data-mapper-bundle)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper-bundle/downloads.png)](https://packagist.org/packages/kassko/data-mapper-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-mapper-bundle/v/unstable.png)](https://packagist.org/packages/kassko/data-mapper-bundle)

This bundle integrates the component data-mapper into Symfony projects. This component is a mapper which gives a lot of features to representate some raw data like objects.

To know more about this component and how to use it, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

Installation on Symfony 2
----------------

Add to your composer.json:
```json
"require": {
    "kassko/data-mapper-bundle": "~0.13.0@alpha"
}
```

Note that:
* the second version number is used when compatibility is broken
* the third for new feature
* the fourth for hotfix
* the first for new API or to go from pre-release to release (from 0 to 1)

Register the bundle to the kernel:
```php
public function registerBundles()
{
    $bundles = array(
        new Kassko\Bundle\DataMapperBundle\KasskoDataMapperBundle(),
        new Kassko\Bundle\ClassResolverBundle\KasskoClassResolverBundle(),
    );
}
```

Services
----------
data-mapper-bundle provides services to facilitate to use data-mapper components:

#### kassko_data_mapper ####
It represents a Kassko\DataMapper\DataMapper instance.

To know more about this component and how to use it, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).


Tags (dependency injection tags)
----------
#### kassko_data_mapper.listener ####
data-mapper need to be able to retrieve an object listener from its full qualified class name. To help it, register your object listener as service and tag it.
To know more about object listener, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

#### kassko_data_mapper.mapping_loader ####
Simplify the work to expose a custom mapping loader.
To know more about custom mapping loader creation (contract to implement etc.), please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

#### kassko_data_mapper.registry_item ####
Facilitate to add a service into the registry.

Tag your service:
```xml
<service id="some_service">
    <tag name="kassko_data_mapper.registry_item" key="some_service_key">
</service>
```

And get your service:
```php
$someService = Registry::getInstance()['some_service_key'];
```
To know more about registry usefulness, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).


Configuration
----------
```yaml
kassko_data_mapper:
    mapping:
        default_resource_type: annotations # Default is "annotations" or other type (1).
        default_resource_dir: # Optional.
        default_provider_method: # Optional.
        bundles: #optional section
            some_bundle:
                resource_type: annotations # Default is "annotations" or other type (1).
                resource_dir: # The resource dir of the given bundle.
                provider_method: ~ # Required. Default value is null.
                objects: # Optional section.
                    some_object:
                        resource_type: # Optional.
                        resource_path: # Optional. The resource directory with the resource name. If not defined, data-mapper fallback to resource_name and prepend to it resource_dir (or default_resource_dir). So if resource_path is not defined, case resource_name and resource_dir (or default_resource_dir) must be defined.
                        resource_name: # Optional. Only the resource name (so without the directory).
                        provider_method: # Optional. Override default_provider_method.
                        object_class: # Required (full qualified object class name).
    cache:
        metadata_cache: # Optional section
            class: # Optional.
            id: # Optional.
            life_time: # Default is 0
            is_shared: # Default is false
            adapter_class: # Default is "Kassko\Bundle\DataMapperBundle\Adapter\Cache\DoctrineCacheAdapter"
        result_cache: # Optional section and same as metadata_cache
    logger_service: # Optional. A logger service name. Il will be used for logging in data-mapper component.
```
(1) availables types are annotations, yaml, php, php_file, yaml_file.
And maybe others if you add some custom mapping loaders.
