<?php

declare(strict_types=1);

namespace JimmyAhalpara\ActivityLogEnricher\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestModel extends Model
{
    use SoftDeletes;

    protected $table = 'test_models';

    protected $fillable = ['name', 'description'];

    /** @var array<string> */
    protected $appends = ['label', 'custom_label'];

    public function getLabelAttribute(): string
    {
        $name = $this->getAttribute('name');
        $id = $this->getAttribute('id');

        return (string) $name . ' (' . (string) $id . ')';
    }

    public function getCustomLabelAttribute(): string
    {
        $name = $this->getAttribute('name');

        return 'Custom: ' . (string) $name;
    }

    public function getLabelMethod(): string
    {
        $name = $this->getAttribute('name');

        return 'Method: ' . (string) $name;
    }
}
