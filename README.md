# Laravel ActivityLog Enricher

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahalpara/laravel-activitylog-enricher.svg?style=flat-square)](https://packagist.org/packages/ahalpara/laravel-activitylog-enricher)
[![Total Downloads](https://img.shields.io/packagist/dt/ahalpara/laravel-activitylog-enricher.svg?style=flat-square)](https://packagist.org/packages/ahalpara/laravel-activitylog-enricher)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/jimmyahalpara/laravel-activitylog-enricher/Tests?label=tests&style=flat-square)](https://github.com/jimmyahalpara/laravel-activitylog-enricher/actions?query=workflow%3ATests+branch%3Amain)
[![PHP Stan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://phpstan.org/)

A Laravel package that enriches [Spatie ActivityLog](https://github.com/spatie/laravel-activitylog) entries by resolving foreign key IDs to human-readable labels, making your audit trails more meaningful and user-friendly.

## Features

- 🔄 **Automatic Foreign Key Resolution**: Convert foreign key IDs to readable labels
- 🏗️ **Nested Array Support**: Handle complex nested structures like `items.*.material_id`
- 🎯 **Flexible Label Sources**: Support for model attributes, accessor methods, or custom methods
- 🗂️ **Predefined Configurations**: Reusable mapping configurations via config file
- 🛡️ **Soft Delete Awareness**: Handles soft-deleted models gracefully
- 🚫 **Error Resilient**: Continues enrichment even when some models are missing
- 🧪 **Fully Tested**: Comprehensive test suite with 100% coverage
- 📋 **Type Safe**: Full PHP 8.1+ type declarations and PHPStan level 8 compliance

## Requirements

- PHP 8.2 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0
- Spatie Laravel ActivityLog 4.0+

## Installation

Install the package via Composer:

```bash
composer require ahalpara/laravel-activitylog-enricher
```

The package will automatically register its service provider.

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --provider="Ahalpara\ActivityLogEnricher\ActivityLogEnricherServiceProvider" --tag="activity-log-enricher-config"
```

## Quick Start

### Basic Usage

Use the enricher in your model's `tapActivity` method:

```php
<?php

use JimmyAhalpara\ActivityLogEnricher\Facades\ActivityLogEnricher;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Job extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        ActivityLogEnricher::enrichActivity($activity, [
            'customer_id' => [
                'class' => Contact::class,
                'label_attribute' => 'name',
                'new_key' => 'customer_name',
            ],
            'design_id' => [
                'class' => Design::class,
                'label_attribute' => 'title',
                'new_key' => 'design_title',
            ],
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

### Before and After Comparison

**Before enrichment:**
```json
{
  "old": {
    "customer_id": 1,
    "design_id": 5
  },
  "attributes": {
    "customer_id": 2,
    "design_id": 5
  }
}
```

**After enrichment:**
```json
{
  "old": {
    "customer_id": 1,
    "customer_name": "ABC Corporation",
    "design_id": 5,
    "design_title": "Premium Package Design"
  },
  "attributes": {
    "customer_id": 2,
    "customer_name": "XYZ Industries",
    "design_id": 5,
    "design_title": "Premium Package Design"
  }
}
```

## Configuration Options

### Field Mapping Configuration

Each field mapping supports the following options:

```php
'foreign_key_name' => [
    'class' => ModelClass::class,           // Required: The model class to resolve
    'label_attribute' => 'attribute_name',  // Optional: Attribute/method for label (default: 'label')
    'new_key' => 'enriched_key_name',       // Optional: New key name (default: guessed from foreign_key)
]
```

### Label Attribute Resolution

The enricher tries multiple methods to resolve labels in this order:

1. **Direct method call**: `$model->getLabelWithoutClass()`
2. **Accessor method**: `$model->getLabelAttribute()`
3. **Direct attribute**: `$model->label`
4. **Appended attribute**: If `label` is in the model's `$appends` array
5. **Fallback**: `(string) $model`

## Advanced Usage

### Nested Array Enrichment

Perfect for enriching line items or nested structures:

```php
ActivityLogEnricher::enrichActivity($activity, [
    'items.*.product_id' => [
        'class' => Product::class,
        'label_attribute' => 'name',
        'new_key' => 'product_name',
    ],
    'items.*.category_id' => [
        'class' => Category::class,
        'label_attribute' => 'title',
        'new_key' => 'category_title',
    ],
]);
```

**Input:**
```json
{
  "attributes": {
    "items": [
      {"product_id": 1, "category_id": 2, "quantity": 5},
      {"product_id": 3, "category_id": 1, "quantity": 2}
    ]
  }
}
```

**Output:**
```json
{
  "attributes": {
    "items": [
      {
        "product_id": 1,
        "product_name": "Laptop Computer",
        "category_id": 2,
        "category_title": "Electronics",
        "quantity": 5
      },
      {
        "product_id": 3,
        "product_name": "Office Chair",
        "category_id": 1,
        "category_title": "Furniture",
        "quantity": 2
      }
    ]
  }
}
```

### Using Predefined Configurations

Define reusable configurations in `config/activity-log-enricher.php`:

```php
'mappings' => [
    'orders' => [
        'customer_id' => [
            'class' => Customer::class,
            'label_attribute' => 'company_name',
            'new_key' => 'customer',
        ],
        'items.*.product_id' => [
            'class' => Product::class,
            'label_attribute' => 'name',
            'new_key' => 'product',
        ],
    ],
],
```

Then use in your model:

```php
public function tapActivity(Activity $activity, string $eventName): void
{
    ActivityLogEnricher::enrichActivityWithConfig($activity, 'orders');
}
```

### Custom Label Methods

Use any model method for label generation:

```php
class Material extends Model
{
    public function getLabelWithoutClass(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }
    
    public function getFullDescriptionAttribute(): string
    {
        return $this->name . ' - ' . $this->description;
    }
}

// Usage
ActivityLogEnricher::enrichActivity($activity, [
    'material_id' => [
        'class' => Material::class,
        'label_attribute' => 'getLabelWithoutClass', // Direct method
    ],
    'secondary_material_id' => [
        'class' => Material::class,
        'label_attribute' => 'full_description', // Accessor attribute
    ],
]);
```

### Automatic Key Guessing

When `new_key` is not specified, the enricher automatically removes `_id` suffix:

```php
ActivityLogEnricher::enrichActivity($activity, [
    'customer_id' => [
        'class' => Customer::class,
        'label_attribute' => 'name',
        // new_key will be automatically set to 'customer'
    ],
    'shipping_address_id' => [
        'class' => Address::class,
        'label_attribute' => 'full_address',
        // new_key will be automatically set to 'shipping_address'
    ],
]);
```

## Real-World Examples

### E-commerce Order System

```php
class Order extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        ActivityLogEnricher::enrichActivity($activity, [
            'customer_id' => [
                'class' => Customer::class,
                'label_attribute' => 'full_name',
                'new_key' => 'customer',
            ],
            'billing_address_id' => [
                'class' => Address::class,
                'label_attribute' => 'formatted_address',
                'new_key' => 'billing_address',
            ],
            'shipping_address_id' => [
                'class' => Address::class,
                'label_attribute' => 'formatted_address',
                'new_key' => 'shipping_address',
            ],
            'items.*.product_id' => [
                'class' => Product::class,
                'label_attribute' => 'name',
                'new_key' => 'product',
            ],
            'items.*.variant_id' => [
                'class' => ProductVariant::class,
                'label_attribute' => 'display_name',
                'new_key' => 'variant',
            ],
        ]);
    }
}
```

### Project Management System

```php
class Task extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        ActivityLogEnricher::enrichActivity($activity, [
            'project_id' => [
                'class' => Project::class,
                'label_attribute' => 'name',
                'new_key' => 'project',
            ],
            'assigned_to' => [
                'class' => User::class,
                'label_attribute' => 'full_name',
                'new_key' => 'assignee',
            ],
            'created_by' => [
                'class' => User::class,
                'label_attribute' => 'full_name',
                'new_key' => 'creator',
            ],
            'attachments.*.document_id' => [
                'class' => Document::class,
                'label_attribute' => 'filename',
                'new_key' => 'document',
            ],
        ]);
    }
}
```

### Manufacturing System (Based on Original Usage)

```php
class Job extends Model
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        ActivityLogEnricher::enrichActivity($activity, [
            'customer_id' => [
                'class' => Contact::class,
                'label_attribute' => 'label',
                'new_key' => 'customer',
            ],
            'design_id' => [
                'class' => Design::class,
                'label_attribute' => 'label',
                'new_key' => 'design',
            ],
            'plate_id' => [
                'class' => Plate::class,
                'label_attribute' => 'label',
                'new_key' => 'plate',
            ],
            'laminate_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'laminate_material',
            ],
            'cap_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'cap_material',
            ],
            'should_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'shoulder_material',
            ],
            'pvc_sleeve_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'pvc_sleeve_material',
            ],
            'liner_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'liner_material',
            ],
            'granules_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'granules_material',
            ],
            'cold_foil_material_id' => [
                'class' => Material::class,
                'label_attribute' => 'getLabelWithoutClass',
                'new_key' => 'cold_foil_material',
            ],
        ]);
    }
}
```

## Error Handling

The enricher is designed to be resilient and will continue processing even when encountering errors:

- **Missing models**: If a foreign key references a non-existent model, the enrichment for that field is skipped
- **Invalid classes**: Throws `InvalidModelException` for configuration errors
- **Missing attributes**: Falls back to string representation of the model
- **Soft deleted models**: Automatically included using `withTrashed()`

### Exception Types

```php
use JimmyAhalpara\ActivityLogEnricher\Exceptions\InvalidModelException;

try {
    ActivityLogEnricher::enrichActivity($activity, $invalidMapping);
} catch (InvalidModelException $e) {
    // Handle configuration errors
    Log::error('ActivityLog enrichment failed: ' . $e->getMessage());
}
```

## Performance Considerations

### Eager Loading

To optimize performance, ensure your models are properly eager loaded before enrichment:

```php
// In your model or controller
$activities = Activity::with(['subject', 'causer'])->get();
```

### Caching

Consider implementing model caching for frequently accessed labels:

```php
class Product extends Model
{
    public function getLabelAttribute(): string
    {
        return Cache::remember(
            "product_label_{$this->id}",
            3600,
            fn() => $this->name . ' (' . $this->sku . ')'
        );
    }
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Run code formatting:

```bash
composer format
```

Run all quality checks:

```bash
composer quality
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Ensure all tests pass: `composer quality`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Submit a pull request

### Development Setup

```bash
git clone https://github.com/jimmyahalpara/laravel-activitylog-enricher.git
cd laravel-activitylog-enricher
composer install
composer quality
```

## Security

If you discover any security-related issues, please email jimmy@ahalpara.in instead of using the issue tracker.

## Credits

- **Jimmy Ahalpara** - [jimmy@ahalpara.in](mailto:jimmy@ahalpara.in)
- **Spatie** - For the excellent [Laravel ActivityLog](https://github.com/spatie/laravel-activitylog) package

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Support

If you find this package useful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting issues
- 💡 Contributing improvements
- 📢 Sharing with others

For support, questions, or feature requests, please [open an issue](https://github.com/jimmyahalpara/laravel-activitylog-enricher/issues) on GitHub.