data-mapper-bundle
==================

[![Latest Stable Version](https://poser.pugx.org/kassko/data-mapper-bundle/v/stable.png)](https://packagist.org/packages/kassko/data-mapper-bundle)
[![Total Downloads](https://poser.pugx.org/kassko/data-mapper-bundle/downloads.png)](https://packagist.org/packages/kassko/data-mapper-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kassko/data-mapper-bundle/v/unstable.png)](https://packagist.org/packages/kassko/data-mapper-bundle)

This bundle integrates the data-mapper component into Symfony applications. Which is a mapper that provides a lot of features to represent some raw data as objects.

To know more about this component and how to use it, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

Installation on Symfony 2
----------------

Add [`kassko/data-mapper-bundle`](https://packagist.org/packages/kassko/data-mapper-bundle) to your `composer.json` file:
```json
"require": {
    "kassko/data-mapper-bundle": "~0.14.2"
}
```

**Note that:**
* The second version number is used when compatibility is broken
* The third for new feature
* The fourth for hotfix
* The first for new API or to go from pre-release to release (from 0 to 1)

Register the bundle in `app/AppKernel.php`:
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
The data-mapper-bundle provides services to facilitate the use of the data-mapper component features:

* [DataMapper service](#data-mapper-service)
* [Expression language integration](#expr-lang-integr)
  - [Expression languages services](#expr-lang-services)
  - [Add a provider](#add-provider)


Tags (dependency injection tags)
----------
#### kassko_data_mapper.listener ####
The data-mapper needs to be able to retrieve an object listener from its full qualified class name. In order to do that, you have to register your object listener as a service and tag it.

To know more about object listener, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).


Summary
-------

Installation
-------

Usage
-------

### Data Mapper service

Get the service from your controller:
```php
$this->get('kassko_data_mapper');
```

It represents a `Kassko\DataMapper\DataMapper` instance. To know more about this component and how to use it, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

### Configuration reference

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
And maybe others, feel free to add custom mapping loaders.

### Expression languages services

### Add a provider

```xml
<service id="my_provider" class="Kassko\Sample\SomeExpressionFunctionProvider">
    <tag name="kassko_data_mapper.expression_function_provider" variable_key="container" variable_value="service_container"/>
</service>
```

With the code above, the container is available in your provider. You can use it:

```php
use Kassko\DataMapper\Expression\ExpressionFunction;
use Kassko\DataMapper\Expression\ExpressionFunctionProviderInterface;

class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'granted',
                function ($arg) {
                    return sprintf('container.get(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['container']->get($value);
                }
            ),
        ];
    }
}
```

### Add a custom loader

DataMapper provide three formats for mapping: `annotations`, `yaml` and `php`. But you can use a custom mapping loader.

For more details about how to implement your custom loader, please read the [data-mapper documentation reference](https://github.com/kassko/data-mapper/blob/master/README.md).

### Use a service in a persistent object without injecting it

You need to add it in the registry. You can do that by this way.

Tag your service:
```xml
<service id="logger">
    <tag name="kassko_data_mapper.registry_item" key="logger">
</service>
```

And then you can get your service from your persistent object:
```php
trait LoggableTrait
{
    private function getLogger()
    {
        return Registry::getInstance()['logger'];
    }
}
```

```php
class Person
{
    use LoggableTrait;

    private $id;
    private $name;
    private $address;

    public function getName()
    {
        if (! isset($this->address)) {
            $this->getLogger()->warning(sprintf('No address for %s', $this->name));
        }
    }
}
```
