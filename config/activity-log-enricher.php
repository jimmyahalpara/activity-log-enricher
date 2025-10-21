<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Enricher Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the ActivityLog Enricher package.
    | You can define predefined field mappings that can be reused across your
    | application for consistent activity log enrichment.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Predefined Field Mappings
    |--------------------------------------------------------------------------
    |
    | Define reusable field mappings that can be used with the enrichActivityWithConfig method.
    | Each mapping should contain the foreign key as the array key and configuration as the value.
    |
    | Configuration format:
    | 'foreign_key' => [
    |     'class' => ModelClass::class,           // Required: The model class to resolve
    |     'label_attribute' => 'attribute_name', // Optional: Attribute/method for label (default: 'label')
    |     'new_key' => 'enriched_key_name',      // Optional: New key name (default: guessed from foreign_key)
    | ]
    |
    | Nested array support:
    | 'items.*.material_id' => [
    |     'class' => Material::class,
    |     'label_attribute' => 'name',
    |     'new_key' => 'material_name',
    | ]
    |
    */

    'mappings' => [
        'default' => [
            // Example mappings - customize according to your application
            // 'user_id' => [
            //     'class' => \App\Models\User::class,
            //     'label_attribute' => 'name',
            //     'new_key' => 'user_name',
            // ],
            // 'category_id' => [
            //     'class' => \App\Models\Category::class,
            //     'label_attribute' => 'title',
            //     'new_key' => 'category_title',
            // ],
        ],

        // You can define multiple mapping configurations
        'products' => [
            // Example for product-related enrichments
            // 'product_id' => [
            //     'class' => \App\Models\Product::class,
            //     'label_attribute' => 'name',
            //     'new_key' => 'product_name',
            // ],
            // 'brand_id' => [
            //     'class' => \App\Models\Brand::class,
            //     'label_attribute' => 'name',
            //     'new_key' => 'brand_name',
            // ],
        ],

        'orders' => [
            // Example for order-related enrichments with nested arrays
            // 'customer_id' => [
            //     'class' => \App\Models\Customer::class,
            //     'label_attribute' => 'full_name',
            //     'new_key' => 'customer_name',
            // ],
            // 'items.*.product_id' => [
            //     'class' => \App\Models\Product::class,
            //     'label_attribute' => 'name',
            //     'new_key' => 'product_name',
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    |
    | Configure global behavior for the enricher.
    |
    */

    'settings' => [
        /*
        |--------------------------------------------------------------------------
        | Default Label Attribute
        |--------------------------------------------------------------------------
        |
        | The default attribute/method to use for generating labels when not
        | explicitly specified in the field mapping configuration.
        |
        */
        'default_label_attribute' => 'label',

        /*
        |--------------------------------------------------------------------------
        | Include Soft Deleted Models
        |--------------------------------------------------------------------------
        |
        | Whether to include soft-deleted models when resolving foreign keys.
        | When true, uses withTrashed() to find models.
        |
        */
        'include_soft_deleted' => true,

        /*
        |--------------------------------------------------------------------------
        | Fail Silently
        |--------------------------------------------------------------------------
        |
        | Whether to fail silently when encountering errors during enrichment.
        | When true, errors are suppressed and enrichment continues.
        | When false, exceptions are thrown on errors.
        |
        */
        'fail_silently' => true,

        /*
        |--------------------------------------------------------------------------
        | Cache Resolved Models
        |--------------------------------------------------------------------------
        |
        | Whether to cache resolved model labels within a single enrichment operation
        | to improve performance when the same foreign key appears multiple times.
        |
        */
        'cache_resolved_models' => true,
    ],
];