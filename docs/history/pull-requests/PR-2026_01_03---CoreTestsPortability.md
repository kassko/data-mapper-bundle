# PR: Portable Integration Tests from Core Library

**Date:** 2026-01-03
**Branch:** `improv/tests/CoreTestsPortability`

## Summary

This PR integrates the portable integration tests from the Core `data-mapper` library into the Symfony Bundle, enabling end-to-end testing of the full integration path from semantic configuration through to hydration.

## Changes

### New Files

#### `tests/TestHelpers/SymfonyDataMapperProvider.php`
Symfony Bundle implementation of `DataMapperProviderInterface`:
- Uses `TestKernel` to boot a real Symfony kernel
- Shares kernel between tests for performance optimization
- Registers custom services in the compiled container
- Provides access to the Symfony container for tests

#### `tests/Integration/Portable/DataMapperPortableTest.php`
Extends Core's `DataMapperPortableTest` with `SymfonyDataMapperProvider` injection.

#### `tests/Integration/Portable/LazyLoadingPortableTest.php`
Extends Core's `LazyLoadingPortableTest` with `SymfonyDataMapperProvider` injection.

#### `tests/Integration/Portable/ServiceResolutionPortableTest.php`
Extends Core's `ServiceResolutionPortableTest` with `SymfonyDataMapperProvider` injection.

### Modified Files

#### `tests/Integration/TestKernel.php`
Simplified by removing unused `$customServices` parameter (services are now registered after boot).

#### `composer.json`
Added Core test namespace to autoload-dev:
```json
"autoload-dev": {
    "psr-4": {
        "Kassko\\Bundle\\DataMapperBundle\\Tests\\": "tests/",
        "Kassko\\DataMapper\\Tests\\": "vendor/kassko/data-mapper/tests/"
    }
}
```

## Architecture

```
┌────────────────────────────────────────────────────────────┐
│                     Bundle Test Suite                       │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │           SymfonyDataMapperProvider                   │  │
│  │                                                       │  │
│  │  ┌─────────────────┐   ┌─────────────────────────┐   │  │
│  │  │   TestKernel    │◄──│  Shared between tests   │   │  │
│  │  │   (Symfony)     │   │  for performance        │   │  │
│  │  └────────┬────────┘   └─────────────────────────┘   │  │
│  │           │                                           │  │
│  │           ▼                                           │  │
│  │  ┌─────────────────┐                                  │  │
│  │  │   Container     │──► kassko_data_mapper.data_mapper│  │
│  │  └─────────────────┘                                  │  │
│  └──────────────────────────────────────────────────────┘  │
│                           │                                 │
│                           ▼                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │        Core Portable Tests (inherited)                │  │
│  │                                                       │  │
│  │  • DataMapperPortableTest                             │  │
│  │  • LazyLoadingPortableTest                            │  │
│  │  • ServiceResolutionPortableTest                      │  │
│  │                                                       │  │
│  │  Fixtures loaded from Core via parent class           │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

## Test Results

| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| Bundle (all) | 66 | 114 | ✅ Pass |
| Portable | 15 | 29 | ✅ Pass |

### Performance Optimization

The `SymfonyDataMapperProvider` shares the Symfony kernel between tests when possible:
- Kernel is only recreated when configuration changes
- Services are registered after boot for flexibility
- Loader is re-registered via `ensureLoaderRegistered()` after registry cleanup

## Usage

### Running Portable Tests

```bash
cd data-mapper-bundle
./vendor/bin/phpunit tests/Integration/Portable
```

### Creating New Bundle Portable Tests

Simply extend the Core test class and inject the provider:

```php
namespace Kassko\Bundle\DataMapperBundle\Tests\Integration\Portable;

use Kassko\Bundle\DataMapperBundle\Tests\TestHelpers\SymfonyDataMapperProvider;
use Kassko\DataMapper\Tests\Integration\Portable\MyPortableTest as CoreMyPortableTest;

class MyPortableTest extends CoreMyPortableTest
{
    public static function setUpBeforeClass(): void
    {
        self::setDataMapperProvider(new SymfonyDataMapperProvider());
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        SymfonyDataMapperProvider::shutdownSharedKernel();
        parent::tearDownAfterClass();
    }
}
```

## Dependencies

This PR requires the corresponding PR in `data-mapper` Core library:
- Branch: `improv/tests/CoreTestsPortability`
- New interface: `DataMapperProviderInterface`
- New trait enhancement: `LocalFixtureAutoloadTrait`
