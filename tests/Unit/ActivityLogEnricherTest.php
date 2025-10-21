<?php

declare(strict_types=1);

namespace Ahalpara\ActivityLogEnricher\Tests\Unit;

use Ahalpara\ActivityLogEnricher\ActivityLogEnricher;
use Ahalpara\ActivityLogEnricher\Exceptions\InvalidModelException;
use Ahalpara\ActivityLogEnricher\Tests\Models\TestModel;
use Ahalpara\ActivityLogEnricher\Tests\TestCase;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

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

    /** @test */
    public function it_enriches_simple_foreign_keys(): void
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
        
        $this->assertEquals('Test Model 1 (1)', $properties->get('old')['test_model']);
        $this->assertEquals('Test Model 2 (2)', $properties->get('attributes')['test_model']);
    }

    /** @test */
    public function it_guesses_new_key_from_foreign_key(): void
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
        
        $this->assertArrayHasKey('test_model', $properties['attributes']);
        $this->assertEquals('Test Model 1 (1)', $properties['attributes']['test_model']);
    }

    /** @test */
    public function it_enriches_nested_array_properties(): void
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
        
        $this->assertEquals('Test Model 1 (1)', $items[0]['material']);
        $this->assertEquals('Test Model 2 (2)', $items[1]['material']);
    }

    /** @test */
    public function it_uses_different_label_attributes(): void
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
        
        $this->assertEquals('Custom: Test Model 1', $properties['attributes']['test_model']);
    }

    /** @test */
    public function it_uses_method_for_label_attribute(): void
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
        
        $this->assertEquals('Method: Test Model 1', $properties['attributes']['test_model']);
    }

    /** @test */
    public function it_handles_null_foreign_keys(): void
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
        
        $this->assertArrayNotHasKey('test_model', $properties['attributes']);
    }

    /** @test */
    public function it_handles_non_existent_models(): void
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
        
        $this->assertArrayNotHasKey('test_model', $properties['attributes']);
    }

    /** @test */
    public function it_throws_exception_for_missing_class(): void
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

    /** @test */
    public function it_throws_exception_for_non_existent_class(): void
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

    /** @test */
    public function it_throws_exception_for_invalid_model_class(): void
    {
        $this->expectException(InvalidModelException::class);
        $this->expectExceptionMessage("Class 'stdClass' must extend Illuminate\\Database\\Eloquent\\Model for field: test_model_id");

        $fieldMappings = [
            'test_model_id' => [
                'class' => \stdClass::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);
    }

    /** @test */
    public function it_handles_empty_properties(): void
    {
        $this->activity->properties = [];

        $fieldMappings = [
            'test_model_id' => [
                'class' => TestModel::class,
                'label_attribute' => 'label',
            ],
        ];

        $this->enricher->enrichActivity($this->activity, $fieldMappings);

        $this->assertEquals([], $this->activity->properties->toArray());
    }

    /** @test */
    public function it_preserves_original_foreign_keys(): void
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
        
        $this->assertEquals(1, $properties['attributes']['test_model_id']);
        $this->assertEquals('value', $properties['attributes']['other_field']);
        $this->assertEquals('Test Model 1 (1)', $properties['attributes']['test_model']);
    }

    /** @test */
    public function it_handles_soft_deleted_models(): void
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
        
        $this->assertStringContainsString('Deleted Model', $properties['attributes']['test_model']);
    }

    private function createTestModels(): void
    {
        $this->artisan('migrate', ['--database' => 'testing']);

        TestModel::create(['name' => 'Test Model 1', 'description' => 'First test model']);
        TestModel::create(['name' => 'Test Model 2', 'description' => 'Second test model']);
    }
}