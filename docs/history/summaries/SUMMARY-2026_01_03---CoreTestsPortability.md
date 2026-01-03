# Summary: Portable Integration Tests from Core Library

**Date:** 2026-01-03
**Branch:** `improv/tests/CoreTestsPortability`

## Objective

Integrate Core library portable integration tests into the Symfony Bundle to enable end-to-end testing from semantic configuration through to hydration, validating the full integration path.

## What This Enables

1. **End-to-End Testing**: Test the complete flow from bundle configuration → DI compilation → DataMapper → Hydration
2. **Configuration Validation**: Ensure semantic configuration is correctly processed
3. **Service Integration**: Verify services are properly wired in the container
4. **Regression Prevention**: Core tests run in Bundle context catch integration issues

## Implementation Details

### SymfonyDataMapperProvider

Key features:
- **Kernel Sharing**: Reuses the Symfony kernel between tests when configuration is compatible
- **Service Registration**: Registers test services after kernel boot for maximum flexibility
- **Loader Re-registration**: Calls `ensureLoaderRegistered()` when LoaderRegistry was cleared

```php
// Kernel is shared when:
$canReuseKernel = self::$sharedKernel !== null 
    && empty($services) 
    && $bundleConfig === self::$sharedKernelConfig;
```

### Fixture Loading

The `LocalFixtureAutoloadTrait` in Core was enhanced to support class inheritance:
- When a test extends a Core test, fixtures are loaded from the parent class directory
- No need to duplicate fixtures in the Bundle

### Test Inheritance Pattern

```
CoreLazyLoadingPortableTest (data-mapper)
        │
        │ extends
        ▼
BundleLazyLoadingPortableTest (data-mapper-bundle)
        │
        │ injects
        ▼
SymfonyDataMapperProvider
```

## Files Changed

### New Files
- `tests/TestHelpers/SymfonyDataMapperProvider.php`
- `tests/Integration/Portable/DataMapperPortableTest.php`
- `tests/Integration/Portable/LazyLoadingPortableTest.php`
- `tests/Integration/Portable/ServiceResolutionPortableTest.php`

### Modified Files
- `tests/Integration/TestKernel.php` - Simplified constructor
- `composer.json` - Added Core tests namespace to autoload-dev

## Test Coverage

| Test Suite | Tests | Assertions | Skipped | Status |
|------------|-------|------------|---------|--------|
| Unit | 23 | 44 | 0 | ✅ |
| Integration | 28 | 41 | 3 | ✅ |
| Portable | 15 | 29 | 0 | ✅ |
| **Total** | **66** | **114** | **3** | ✅ |

## Performance

| Metric | Value |
|--------|-------|
| Portable tests execution | ~7-8 seconds |
| Kernel boots | 2-3 (shared between tests) |
| Memory usage | ~28 MB |

The kernel sharing optimization significantly reduces test execution time compared to booting a fresh kernel for each test.

## Future Improvements

1. **More Portable Tests**: Add tests for CustomHydrator, Context, Expression, etc.
2. **Configuration Matrix**: Test different bundle configurations
3. **Symfony Version Matrix**: Test across Symfony 5.4, 6.x, 7.x
4. **Performance Profiling**: Add profiler integration tests

## Related Changes

- **data-mapper Core**: `improv/tests/CoreTestsPortability` branch
  - New `DataMapperProviderInterface`
  - New `NativeDataMapperProvider`
  - New `PortableIntegrationTestCase`
  - Enhanced `LocalFixtureAutoloadTrait`
  - New `ensureLoaderRegistered()` method in DataMapper
  - Renamed `addLocator()` → `addServiceLocator()`
