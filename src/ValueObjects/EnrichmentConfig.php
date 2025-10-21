<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\ValueObjects;

use Ahalpara\ActivityLogEnricher\Contracts\EnrichmentConfigInterface;

/**
 * Value object representing enrichment configuration for a field.
 */
final readonly class EnrichmentConfig implements EnrichmentConfigInterface
{
    public function __construct(
        private string $foreignKey,
        private string $modelClass,
        private string $labelAttribute = 'label',
        private string $newKey = '',
    ) {}

    /**
     * Get the foreign key field name.
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Get the model class name.
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Get the label attribute name.
     */
    public function getLabelAttribute(): string
    {
        return $this->labelAttribute;
    }

    /**
     * Get the new key name for the enriched value.
     */
    public function getNewKey(): string
    {
        return $this->newKey ?: $this->guessNewKey();
    }

    /**
     * Check if this is a nested pattern (contains .*.).
     */
    public function isNestedPattern(): bool
    {
        return str_contains($this->foreignKey, '.*.');
    }

    /**
     * Get nested keys for array patterns.
     * Returns [arrayKey, nestedKey] for patterns like 'items.*.material_id'.
     *
     * @return array{0: string, 1: string}
     */
    public function getNestedKeys(): array
    {
        if (! $this->isNestedPattern()) {
            return ['', ''];
        }

        $parts = explode('.*.', $this->foreignKey, 2);

        return [$parts[0], $parts[1]];
    }

    /**
     * Guess the new key name from foreign key.
     * Removes '_id' suffix if present.
     */
    private function guessNewKey(): string
    {
        $key = $this->foreignKey;

        // For nested patterns, use the nested key part
        if ($this->isNestedPattern()) {
            [, $key] = $this->getNestedKeys();
        }

        if (str_ends_with($key, '_id')) {
            return substr($key, 0, -3);
        }

        return $key;
    }
}
