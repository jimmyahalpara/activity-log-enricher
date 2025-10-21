<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\Tests\Unit;

use Ahalpara\ActivityLogEnricher\ActivityLogEnricher;
use Ahalpara\ActivityLogEnricher\Exceptions\InvalidModelException;
use Ahalpara\ActivityLogEnricher\Tests\Models\TestModel;
use Ahalpara\ActivityLogEnricher\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ActivityLogEnricherSimpleTest extends TestCase
{
    private ActivityLogEnricher $enricher;

    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enricher = new ActivityLogEnricher();
        $this->activity = new Activity();

        // Create test data
        $this->createTestModels();
    }

    public function testItCanEnrichSimpleProperties(): void
    {
        $this->activity->properties = collect([
            'attributes' => ['test_model_id' => 1],
        ]);

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties->toArray();

        self::assertArrayHasKey('test_model', $properties['attributes']);
        self::assertStringContainsString('Test Model 1', $properties['attributes']['test_model']);
    }

    public function testItThrowsExceptionForMissingClass(): void
    {
        $this->expectException(InvalidModelException::class);

        $fieldMappings = [
            'test_model_id' => [
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);
    }

    public function testItHandlesEmptyProperties(): void
    {
        $this->activity->properties = collect([]);

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        self::assertSame([], $this->activity->properties->toArray());
    }

    private function createTestModels(): void
    {
        $this->artisan('migrate', ['--database' => 'testing']);

        TestModel::create(['name' => 'Test Model 1', 'description' => 'First test model']);
        TestModel::create(['name' => 'Test Model 2', 'description' => 'Second test model']);
    }
}
