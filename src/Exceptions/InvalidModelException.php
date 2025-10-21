<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid model is encountered during enrichment.
 */
final class InvalidModelException extends InvalidArgumentException
{
    /**
     * Create a new invalid model exception.
     */
    public static function missingClass(string $foreignKey): self
    {
        return new self("Missing 'class' key for field mapping: {$foreignKey}");
    }

    /**
     * Create exception for non-existent model class.
     */
    public static function classNotFound(string $className, string $foreignKey): self
    {
        return new self("Model class '{$className}' does not exist for field: {$foreignKey}");
    }

    /**
     * Create exception for invalid model class.
     */
    public static function invalidModel(string $className, string $foreignKey): self
    {
        return new self("Class '{$className}' must extend Illuminate\\Database\\Eloquent\\Model for field: {$foreignKey}");
    }
}
