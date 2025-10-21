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
        return $this->getAttribute('name') . ' (' . $this->getAttribute('id') . ')';
    }

    public function getCustomLabelAttribute(): string
    {
        return 'Custom: ' . $this->getAttribute('name');
    }

    public function getLabelMethod(): string
    {
        return 'Method: ' . $this->getAttribute('name');
    }
}
