<?php

declare(strict_types=1);

namespace JimmyAhalpara\ActivityLogEnricher\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void enrichActivity(\Spatie\Activitylog\Models\Activity $activity, array<string, array<string, mixed>> $fieldMappings)
 * @method static void enrichActivityWithConfig(\Spatie\Activitylog\Models\Activity $activity, string $configKey = 'default')
 *
 * @see \JimmyAhalpara\ActivityLogEnricher\ActivityLogEnricher
 */
final class ActivityLogEnricher extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'activity-log-enricher';
    }
}
