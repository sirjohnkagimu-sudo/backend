<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Unit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
