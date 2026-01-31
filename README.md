# DataMapper Symfony Bundle

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E5.4%7C%5E6.0%7C%5E7.0-green.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](LICENSE.md)

Symfony integration for the [DataMapper](https://github.com/kassko/data-mapper) library.

This bundle provides seamless configuration and service integration to use DataMapper within Symfony applications, including:

- **Automatic DataMapper initialization** via bundle boot
- **Symfony container integration** for service resolution
- **Web Profiler integration** for data lineage visualization
- **Console commands** for metadata validation
- **Full configuration support** via Symfony config

## Requirements

- PHP 8.1 or higher
- Symfony 5.4, 6.x, or 7.x
- DataMapper library v2.40+

## Installation

```bash
composer require kassko/data-mapper-bundle:^2.8-rc@rc
```

### Enable the Bundle

If you're not using Symfony Flex, add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Kassko\Bundle\DataMapperBundle\KasskoDataMapperBundle::class => ['all' => true],
];
```

## Configuration

Create or update `config/packages/kassko_data_mapper.yaml`:

```yaml
# config/packages/kassko_data_mapper.yaml
kassko_data_mapper:
    # Enable data lineage collection for debugging (default: false)
    enable_lineage_collection: false
    
    # Enable cascade collection for attribute inheritance tracking (default: false)
    enable_cascade_collection: false
    
    # Enable Symfony Web Profiler integration (default: true)
    enable_profiler: true
    
    # Data source cache configuration
    data_source_cache:
        enabled: false
        service: null  # PSR-16 cache service ID (e.g., 'cache.app')
    
    # Mapping cache configuration (for MappingStrategy conversions)
    mapping_cache:
        enabled: false
        service: null  # PSR-16 cache service ID (e.g., 'cache.app')
    
    # Mapping strategy feature (disabled by default for performance)
    mapping_strategy:
        enabled: false  # Set to true to use MappingStrategy attribute
    
    # Logger configuration
    logger:
        enabled: true
        service: 'logger'  # PSR-3 logger service ID
        channel: 'data_mapper'  # Monolog channel name
    
    # Validation configuration
    validation:
        paths: []  # Paths to scan for data object classes
        namespaces: []  # Namespaces for class resolution

    # Custom hydrators (name => service_id)
    custom_hydrators: []
    
    # Custom object mappers (name => service_id)
    custom_object_mappers: []
    
    # Global sensitive keys configuration for data lineage (key => level)
    # Levels: 'show', 'mask', 'hide'
    sensitive_keys: []
    
    # Default sensitive level for all properties in data lineage
    # Values: 'show', 'mask', 'hide'
    default_sensitive_level: 'show'
```

If you intend to use the Symfony profiler, update `config/packages/twig.yaml` by adding the path of DataMapper collector Twig template:
```yaml
# config/packages/twig.yaml
twig:
    paths:
        '%kernel.project_dir%/vendor/kassko/data-mapper-bundle/templates': DataMapper    
```

### Minimal Configuration

For most cases, the default configuration works out of the box:

```yaml
kassko_data_mapper: ~
```

### Development Configuration

For development environments with full debugging:

```yaml
# config/packages/dev/kassko_data_mapper.yaml
kassko_data_mapper:
    enable_lineage_collection: true
    enable_cascade_collection: true
    enable_profiler: true
```

### Sensitive Data Handling

You can configure how sensitive data appears in lineage collection and debugging:

```yaml
kassko_data_mapper:
    enable_lineage_collection: true
    
    # Define sensitive keys globally
    sensitive_keys:
        password: hide      # Completely hide the value
        api_key: mask       # Show first/last characters: "sk_****yz"
        email: show         # Show the full value
    
    # Default level for all properties (show, mask, or hide)
    default_sensitive_level: show
```

This protects sensitive data in:
- Profiler output
- Debug logs
- Data lineage exports

### Custom Hydrators

Register custom hydrators for special data types. You can register them via configuration:

```yaml
kassko_data_mapper:
    custom_hydrators:
        datetime: 'app.hydrator.datetime'
        money: 'app.hydrator.money'
```

Or via service tags:

```yaml
services:
    App\Hydrator\DateTimeHydrator:
        tags:
            - { name: 'kassko_data_mapper.custom_hydrator', key: 'datetime' }
    
    App\Hydrator\MoneyHydrator:
        tags:
            - { name: 'kassko_data_mapper.custom_hydrator', key: 'money' }
```

Then use them in your data objects:

```php
use Kassko\DataMapper\Attribute\CustomHydrator;

class Product
{
    #[CustomHydrator('money')]
    private Money $price;
}
```

> **Note:** Each hydrator key must be unique. The bundle will throw a `DuplicateKeyException` if the same key is defined in both configuration and tags, or if duplicated among tagged services.

### Custom Object Mappers

Similar to hydrators, you can register custom object mappers via configuration:

```yaml
kassko_data_mapper:
    custom_object_mappers:
        product: 'app.object_mapper.product'
        order: 'app.object_mapper.order'
```

Or via service tags:

```yaml
services:
    App\ObjectMapper\ProductMapper:
        tags:
            - { name: 'kassko_data_mapper.custom_object_mapper', key: 'product' }
```

> **Note:** Each object mapper key must be unique, same as hydrators.

### Mapping strategy and Mapping Cache

```yaml
kassko_data_mapper:
    mapping_strategy:
        enabled: true
    mapping_cache:
        enabled: true
        service: 'cache.app'  # PSR-16 cache service
```

## Usage

### HandleObject Value Resolver

The bundle provides a `#[HandleObject]` attribute for controller action parameters that automatically hydrates objects from request data (requires Symfony 6.0 or higher):

```php
use Kassko\Bundle\DataMapperBundle\Attribute\HandleObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController
{
    #[Route('/products/{barcode}', name: 'api_products_get', methods: ['GET'])]
    public function getProduct(
        #[HandleObject([
            'request:barcode' => 'barcode',              // Route parameter
            'header:Tenant-Id' => 'tenantId',            // Header (case-insensitive)
            'header:feature-flags' => 'context:featureFlags', // Store in context
            'header:x-request-id' => 'context:requestId',     // Optional X- headers
        ])]
        Product $product,
    ): JsonResponse {
        $tenant = $product->getTenant();
        // ...
    }
}
```

**Mapping sources:**
- `request:param` - Route parameters
- `query:param` - Query string parameters
- `header:Name` - HTTP headers (case-insensitive)
- `body:field` - JSON body fields

**Target types:**
- `propertyName` - Maps to object property
- `context:key` - Stores in DataMapper hydration context

**Optional headers:** Headers prefixed with `X-` are optional and won't cause errors if missing.

### Accessing the DataMapper

The DataMapper is available as a service and can be autowired:

```php
use Kassko\DataMapper\DataMapper;

class MyController
{
    public function __construct(
        private DataMapper $dataMapper
    ) {}
    
    public function index(): Response
    {
        // Use context to pass data to your data objects
        $this->dataMapper->addToContext('current_user', $user);
        $this->dataMapper->addToContext('locale', $request->getLocale());
        
        // Your data objects can now access this context
        // ...
    }
}
```

### Using Services as Data Sources

The bundle integrates with Symfony's service container, allowing you to use services as data sources:

```php
use Kassko\DataMapper\Attribute\DataSource;
use Kassko\DataMapper\Attribute\Id;

class User
{
    #[Id]
    private int $id;
    
    private string $name;
    
    // Use a Symfony service as data source
    #[DataSource(class: '@app.user_repository', method: 'findOrdersForUser')]
    private array $orders = [];
}
```

The `@` prefix indicates a service ID lookup in the container.

### Console Commands

The bundle provides validation commands to check your data object metadata:

```bash
# Validate a specific class
php bin/console datamapper:validate:class 'App\Entity\User'

# Validate all classes in a directory
php bin/console datamapper:validate src/Entity --namespace='App\Entity'
```

### Web Profiler Integration

When `enable_profiler` and `enable_lineage_collection` are enabled, you'll see a DataMapper panel in the Symfony Web Profiler showing:

- **Event Timeline**: Chronological view of all data operations
- **By Class**: Events grouped by data object class
- **By Type**: Events grouped by operation type (hydration, datasource call, etc.)

The profiler displays:
- DataSource calls and their results
- Property hydrations and transformations
- Skipped properties with reasons
- Hook executions
- Maximum hydration depth
- Total operation duration

## How It Works

### Bundle Initialization

The DataMapper context is initialized during the bundle's `boot()` phase. This ensures that:

1. The `Loader` is registered in the `LoaderRegistry`
2. The `ContextRegistry` is configured with logging
3. All data objects can access lazy loading capabilities

This design keeps data objects **serializable** (no direct dependency on the loader) while providing full functionality.

### Service Resolution

The bundle creates a `ServiceResolver` backed by Symfony's container, allowing:

- Service ID lookups with `@service_id` prefix
- Direct class instantiation as fallback
- Factory services and callables support

## Testing

Run the test suite:

```bash
composer install
./vendor/bin/phpunit
```

## Architecture

```
src/
├── KasskoDataMapperBundle.php     # Main bundle class with boot initialization
├── Attribute/
│   └── HandleObject.php           # Controller parameter attribute
├── DependencyInjection/
│   ├── Configuration.php          # Bundle configuration definition
│   ├── KasskoDataMapperExtension.php # Service container extension
│   └── Compiler/
│       ├── CustomHydratorPass.php     # Tagged hydrator collector
│       └── CustomObjectMapperPass.php # Tagged object mapper collector
├── DataCollector/
│   └── DataMapperDataCollector.php # Symfony Profiler integration
├── Exception/
│   └── DuplicateKeyException.php  # Thrown on duplicate hydrator/mapper keys
├── Service/
│   ├── DataMapperFactory.php      # Factory for DataMapper with enum conversion
│   ├── ServiceResolverFactory.php # Creates ServiceResolver with container
│   └── DataMapperConfigurator.php # Post-construction DataMapper setup
└── ValueResolver/
    └── HandleObjectValueResolver.php # Controller parameter value resolver (requires Symfony 6.0 or higher)

config/
└── services.yaml                  # Service definitions

templates/
└── Collector/
    └── data_mapper.html.twig      # Profiler panel template
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for details on contributing to this bundle.

## License

This bundle is released under the Apache 2.0 License. See [LICENSE.md](LICENSE.md) for details.

## Links

- [DataMapper Core Library](https://github.com/kassko/data-mapper)
- [DataMapper Documentation](https://github.com/kassko/data-mapper/blob/2.0/README.md)

