# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0-alpha] - 2025-12-27

### Added

- **Complete rewrite** for DataMapper v2.x compatibility
- **PHP 8.1+ requirement** with modern type declarations
- **Symfony 5.4/6.x/7.x support** via flexible dependency constraints
- **DataMapper initialization in bundle boot** to set up context registries
- **ServiceResolver with Symfony container** integration for service lookups
- **Data Lineage Profiler** integration with Symfony Web Profiler
  - Event timeline visualization
  - Events grouped by class or type
  - Statistics: event count, datasource calls, hydrations, skipped properties
  - Duration and depth tracking
- **Console commands** for metadata validation
  - `datamapper:validate:class` - Validate a specific class
  - `datamapper:validate` - Validate all classes in a directory
- **Flexible configuration** via Symfony config
  - Toggle lineage collection on/off
  - Enable/disable profiler integration
  - Configure cache service (PSR-16)
  - Configure logger service (PSR-3) with channel support
  - Specify validation paths and namespaces
- **Comprehensive test suite**
  - Unit tests for all components
  - Integration tests with real Symfony kernel
- **Full documentation** with examples

### Changed

- **Namespace changed** from `Kassko\Bundle\KasskoDataMapperBundle` to `Kassko\Bundle\DataMapperBundle`
- **Bundle class renamed** to `DataMapperBundle`
- **Extension alias** is now `kassko_data_mapper`

### Removed

- Legacy code from v1.x that was incompatible with DataMapper v2.x
- Old configuration options that no longer apply

## [1.x] - Previous releases

See the 1.0 branch for the changelog of previous versions that work with DataMapper v1.x.
