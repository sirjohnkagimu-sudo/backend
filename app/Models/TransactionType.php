<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TransactionType extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'department',
        'name',
        'color',
        'icon',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
