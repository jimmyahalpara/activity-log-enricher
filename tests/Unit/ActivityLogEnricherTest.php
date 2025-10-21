<?php

declare(strict_types=1);

namespace JimmyAhalpara\ActivityLogEnricher\Tests\Unit;

use JimmyAhalpara\ActivityLogEnricher\ActivityLogEnricher;
use JimmyAhalpara\ActivityLogEnricher\Exceptions\InvalidModelException;
use JimmyAhalpara\ActivityLogEnricher\Tests\Models\TestModel;
use JimmyAhalpara\ActivityLogEnricher\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;
use stdClass;

class ActivityLogEnricherTest extends TestCase
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

    public function testItEnrichesSimpleForeignKeys(): void
    {
        $this->activity->properties = collect([
            'old' => ['test_model_id' => 1],
            'attributes' => ['test_model_id' => 2],
        ]);

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertSame('Test Model 1 (1)', $properties->get('old')['test_model']);
        self::assertSame('Test Model 2 (2)', $properties->get('attributes')['test_model']);
    }

    public function testItGuessesNewKeyFromForeignKey(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => 1],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertArrayHasKey('test_model', $properties['attributes']);
        self::assertSame('Test Model 1 (1)', $properties['attributes']['test_model']);
    }

    public function testItEnrichesNestedArrayProperties(): void
    {
        $this->activity->properties = [
            'attributes' => [
                'items' => [
                    ['material_id' => 1, 'quantity' => 5],
                    ['material_id' => 2, 'quantity' => 3],
                ],
            ],
        ];

        $fieldMappings = [
            'items.*.material_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'material',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;
        $items = $properties['attributes']['items'];

        self::assertSame('Test Model 1 (1)', $items[0]['material']);
        self::assertSame('Test Model 2 (2)', $items[1]['material']);
    }

    public function testItUsesDifferentLabelAttributes(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => 1],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'custom_label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertSame('Custom: Test Model 1', $properties['attributes']['test_model']);
    }

    public function testItUsesMethodForLabelAttribute(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => 1],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'getLabelMethod',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertSame('Method: Test Model 1', $properties['attributes']['test_model']);
    }

    public function testItHandlesNullForeignKeys(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => null],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertArrayNotHasKey('test_model', $properties['attributes']);
    }

    public function testItHandlesNonExistentModels(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => 999],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertArrayNotHasKey('test_model', $properties['attributes']);
    }

    public function testItThrowsExceptionForMissingClass(): void
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage("Missing 'class' key for field mapping: test_model_id");

        $fieldMappings = [
            'test_model_id' => [
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);
    }

    public function testItThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage("Model class 'NonExistentClass' does not exist for field: test_model_id");

        $fieldMappings = [
            'test_model_id' => [
                'class' => 'NonExistentClass',
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);
    }

    public function testItThrowsExceptionForInvalidModelClass(): void
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage("Class 'stdClass' must extend Illuminate\\Database\\Eloquent\\Model for field: test_model_id");

        $fieldMappings = [
            'test_model_id' => [
                'class' => stdClass::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);
    }

    public function testItHandlesEmptyProperties(): void
    {
        $this->activity->properties = [];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        self::assertSame([], $this->activity->properties->toArray());
    }

    public function testItPreservesOriginalForeignKeys(): void
    {
        $this->activity->properties = [
            'attributes' => ['test_model_id' => 1, 'other_field' => 'value'],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertSame(1, $properties['attributes']['test_model_id']);
        self::assertSame('value', $properties['attributes']['other_field']);
        self::assertSame('Test Model 1 (1)', $properties['attributes']['test_model']);
    }

    public function testItHandlesSoftDeletedModels(): void
    {
        // Create and soft delete a model
        $model = TestModel::create(['name' => 'Deleted Model', 'description' => 'Test']);
        $model->delete();

        $this->activity->properties = [
            'attributes' => ['test_model_id' => $model->id],
        ];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
                'new_key' => 'test_model',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $properties = $this->activity->properties;

        self::assertStringContainsString('Deleted Model', $properties['attributes']['test_model']);
    }

    private function createTestModels(): void
    {
        $this->artisan('migrate', ['--database' => 'testing']);

        TestModel::create(['name' => 'Test Model 1', 'description' => 'First test model']);
        TestModel::create(['name' => 'Test Model 2', 'description' => 'Second test model']);
    }
}
