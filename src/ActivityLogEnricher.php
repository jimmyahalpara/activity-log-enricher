<?php

declare(strict_types=1);

namespace JimmyAhalpara\ActivityLogEnricher;

use JimmyAhalpara\ActivityLogEnricher\Contracts\EnrichmentConfigInterface;
use JimmyAhalpara\ActivityLogEnricher\Exceptions\InvalidModelException;
use JimmyAhalpara\ActivityLogEnricher\ValueObjects\EnrichmentConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Throwable;

use function array_key_exists;
use function in_array;
use function is_array;

/**
 * ActivityLogEnricher - Enriches Spatie ActivityLog entries by resolving foreign key IDs to readable labels.
 *
 * This class provides functionality to enhance activity log entries by converting foreign key references
 * into human-readable labels, making audit trails more meaningful and user-friendly.
 */
final class ActivityLogEnricher
{
    /**
     * Enrich activity properties by resolving foreign key IDs to readable labels.
     *
     * @param Activity $activity The activity log entry to enrich
     * @param array<string, array<string, mixed>> $fieldMappings Field mapping configuration
     *
     * @throws InvalidModelException When a model class doesn't exist or isn't valid
     *
     * @example
     * ActivityLogEnricher::enrichActivity($activity, [
     *     'contact_id' => [
     *         'class' => Contact::class,
     *         'label_attribute' => 'name',
     *         'new_key' => 'customer'
     *     ],
     *     'items.*.material_id' => [
     *         'class' => Material::class,
     *         'label_attribute' => 'label'
     *     ]
     * ]);
     */
    public function enrichActivity(Activity $activity, array $fieldMappings): void
    {
        $enrichmentConfigs = $this->buildEnrichmentConfigs($fieldMappings);

        $properties = $activity->properties ?? collect();
        $old = $properties->get('old', []);
        $attributes = $properties->get('attributes', []);

        // Ensure we have arrays
        $old = is_array($old) ? $old : [];
        $attributes = is_array($attributes) ? $attributes : [];

        foreach ($enrichmentConfigs as $config) {
            if ($config->isNestedPattern()) {
                $this->enrichNestedArrayProperties($old, $attributes, $config);
            } else {
                $this->enrichSimpleProperties($old, $attributes, $config);
            }
        }

        $this->updateActivityProperties($activity, $old, $attributes);
    }

    /**
     * Enrich activity using predefined configuration from config file.
     *
     * @param Activity $activity The activity log entry to enrich
     * @param string $configKey Configuration key from config file
     */
    public function enrichActivityWithConfig(Activity $activity, string $configKey = 'default'): void
    {
        /** @var array<string, array<string, mixed>> $config */
        $config = config("activity-log-enricher.mappings.{$configKey}", []);

        if (empty($config) || ! is_array($config)) {
            return;
        }

        $this->enrichActivity($activity, $config);
    }

    /**
     * Build enrichment configuration objects from array mappings.
     *
     * @param array<string, array<string, mixed>> $fieldMappings
     *
     * @return Collection<int, EnrichmentConfigInterface>
     */
    private function buildEnrichmentConfigs(array $fieldMappings): Collection
    {
        return collect($fieldMappings)->map(function (array $config, string $foreignKey): EnrichmentConfig {
            $this->validateMappingConfig($config, $foreignKey);

            $labelAttribute = $config['label_attribute'] ?? 'label';
            $newKey = $config['new_key'] ?? $this->guessNewKey($foreignKey);

            // After validation, we know class is a string
            assert(is_string($config['class']));

            return new EnrichmentConfig(
                foreignKey: $foreignKey,
                modelClass: $config['class'],
                labelAttribute: is_string($labelAttribute) ? $labelAttribute : 'label',
                newKey: is_string($newKey) ? $newKey : $foreignKey
            );
        })->values();
    }

    /**
     * Validate mapping configuration.
     *
     * @param array<string, mixed> $config
     *
     * @throws InvalidModelException
     */
    private function validateMappingConfig(array $config, string $foreignKey): void
    {
        if (! isset($config['class'])) {
            throw new InvalidModelException("Missing 'class' key for field mapping: {$foreignKey}");
        }

        if (! is_string($config['class'])) {
            throw new InvalidModelException("Class name must be a string for field: {$foreignKey}");
        }

        $className = $config['class'];

        if (! class_exists($className)) {
            throw new InvalidModelException("Model class '{$className}' does not exist for field: {$foreignKey}");
        }

        if (! is_subclass_of($className, Model::class)) {
            throw new InvalidModelException("Class '{$className}' must extend Illuminate\\Database\\Eloquent\\Model for field: {$foreignKey}");
        }
    }

    /**
     * Enrich nested array properties (e.g., 'items.*.material_id').
     *
     * @param array<string, mixed> $old
     * @param array<string, mixed> $attributes
     */
    private function enrichNestedArrayProperties(array &$old, array &$attributes, EnrichmentConfigInterface $config): void
    {
        [$arrayKey, $nestedKey] = $config->getNestedKeys();

        if (isset($old[$arrayKey]) && is_array($old[$arrayKey])) {
            /** @var array<int, mixed> $oldArray */
            $oldArray = $old[$arrayKey];
            $old[$arrayKey] = $this->enrichNestedArray($oldArray, $nestedKey, $config);
        }

        if (isset($attributes[$arrayKey]) && is_array($attributes[$arrayKey])) {
            /** @var array<int, mixed> $attributesArray */
            $attributesArray = $attributes[$arrayKey];
            $attributes[$arrayKey] = $this->enrichNestedArray($attributesArray, $nestedKey, $config);
        }
    }

    /**
     * Enrich simple top-level properties.
     *
     * @param array<string, mixed> $old
     * @param array<string, mixed> $attributes
     */
    private function enrichSimpleProperties(array &$old, array &$attributes, EnrichmentConfigInterface $config): void
    {
        $foreignKey = $config->getForeignKey();
        $newKey = $config->getNewKey();

        if (isset($old[$foreignKey])) {
            $label = $this->resolveModelLabel($old[$foreignKey], $config);

            if ($label !== null) {
                $old[$newKey] = $label;
            }
        }

        if (isset($attributes[$foreignKey])) {
            $label = $this->resolveModelLabel($attributes[$foreignKey], $config);

            if ($label !== null) {
                $attributes[$newKey] = $label;
            }
        }
    }

    /**
     * Enrich nested array items with model labels.
     *
     * @param array<int, mixed> $items
     *
     * @return array<int, mixed>
     */
    private function enrichNestedArray(array $items, string $nestedKey, EnrichmentConfigInterface $config): array
    {
        foreach ($items as $index => $item) {
            if (is_array($item) && array_key_exists($nestedKey, $item)) {
                $foreignKeyValue = $item[$nestedKey];
                $label = $this->resolveModelLabel($foreignKeyValue, $config);

                if ($label !== null && is_array($items[$index])) {
                    $items[$index][$config->getNewKey()] = $label;
                }
            }
        }

        return $items;
    }

    /**
     * Resolve model label from foreign key value.
     *
     * @param mixed $foreignKeyValue
     */
    private function resolveModelLabel($foreignKeyValue, EnrichmentConfigInterface $config): ?string
    {
        if ($foreignKeyValue === null || $foreignKeyValue === '') {
            return null;
        }

        try {
            $modelClass = $config->getModelClass();
            $model = $modelClass::withTrashed()->find($foreignKeyValue);

            if (! $model) {
                return null;
            }

            return $this->getModelLabel($model, $config->getLabelAttribute());
        } catch (Throwable) {
            // Log error if needed, but don't break the enrichment process
            return null;
        }
    }

    /**
     * Get label from model using specified attribute or method.
     */
    private function getModelLabel(Model $model, string $labelAttribute): string
    {
        // First, check if it's a direct method on the model (e.g., getLabelWithoutClass)
        if (method_exists($model, $labelAttribute)) {
            $result = $model->{$labelAttribute}();

            return (string) $result;
        }

        // Then, try as attribute accessor method (e.g., getLabelAttribute)
        $methodName = 'get' . ucfirst($labelAttribute) . 'Attribute';

        if (method_exists($model, $methodName)) {
            $result = $model->{$methodName}();

            return (string) $result;
        }

        // Try as direct attribute (including appended attributes)
        try {
            $attributes = $model->getAttributes();

            if (array_key_exists($labelAttribute, $attributes)) {
                return (string) $model->{$labelAttribute};
            }

            // Check if it's an appended attribute
            if (in_array($labelAttribute, $model->getAppends(), true)) {
                return (string) $model->{$labelAttribute};
            }
        } catch (Throwable) {
            // Continue to fallback
        }

        // Fallback to string representation
        return (string) $model;
    }

    /**
     * Guess the new key name from foreign key.
     * Removes '_id' suffix if present.
     */
    private function guessNewKey(string $foreignKey): string
    {
        if (str_ends_with($foreignKey, '_id')) {
            return substr($foreignKey, 0, -3);
        }

        return $foreignKey;
    }

    /**
     * Update activity properties with enriched data.
     *
     * @param array<string, mixed> $old
     * @param array<string, mixed> $attributes
     */
    private function updateActivityProperties(Activity $activity, array $old, array $attributes): void
    {
        $newProperties = [];

        if (! empty($old)) {
            $newProperties['old'] = $old;
        }

        if (! empty($attributes)) {
            $newProperties['attributes'] = $attributes;
        }

        $activity->properties = collect($newProperties);
    }
}
