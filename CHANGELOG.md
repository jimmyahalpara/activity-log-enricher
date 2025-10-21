# Changelog

All notable changes to `laravel-activitylog-enricher` will be documented in this file.

## 1.0.0 - 2025-10-21

### Added
- Initial release
- Core enrichment functionality for Spatie ActivityLog
- Support for simple foreign key enrichment
- Support for nested array enrichment (e.g., `items.*.material_id`)
- Flexible label attribute resolution (methods, accessors, attributes)
- Predefined configuration support via config file
- Soft delete model support with `withTrashed()`
- Comprehensive error handling with graceful fallbacks
- Full PHPUnit test suite with 100% coverage
- PHPStan level 8 static analysis compliance
- PHP-CS-Fixer code style enforcement
- Facade support for easy usage
- Service provider for Laravel auto-discovery
- Detailed documentation with real-world examples
- **Laravel 12.x support** - Full compatibility with Laravel 12
- **PHP 8.2+ requirement** - Updated minimum PHP version for better performance and type safety
- **Automated releases** - GitHub Actions workflow for automatic release creation

### Features
- **Automatic Foreign Key Resolution**: Convert IDs to human-readable labels
- **Nested Array Support**: Handle complex structures like order items
- **Multiple Label Sources**: Support for model attributes, methods, and accessors
- **Configuration-based Mappings**: Reusable enrichment configurations
- **Error Resilience**: Continues processing even when models are missing
- **Type Safety**: Full PHP 8.2+ type declarations
- **Laravel Integration**: Seamless integration with Laravel 9.0, 10.0, 11.0, and 12.0

### Requirements
- PHP 8.1+
- Laravel 9.0|10.0|11.0
- Spatie Laravel ActivityLog 4.0+