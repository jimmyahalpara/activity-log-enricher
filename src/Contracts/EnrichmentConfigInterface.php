<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\Contracts;

/**
 * Interface for enrichment configuration objects.
 */
interface EnrichmentConfigInterface
{
    /**
     * Get the foreign key field name.
     */
    public function getForeignKey(): string;

    /**
     * Get the model class name.
     */
    public function getModelClass(): string;

    /**
     * Get the label attribute name.
     */
    public function getLabelAttribute(): string;

    /**
     * Get the new key name for the enriched value.
     */
    public function getNewKey(): string;

    /**
     * Check if this is a nested pattern (contains .*.).
     */
    public function isNestedPattern(): bool;

    /**
     * Get nested keys for array patterns.
     * Returns [arrayKey, nestedKey] for patterns like 'items.*.material_id'.
     *
     * @return array{0: string, 1: string}
     */
    public function getNestedKeys(): array;
}