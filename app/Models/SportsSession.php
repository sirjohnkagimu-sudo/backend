<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SportsSession extends Model
{
    use HasFactory;

    protected $table = 'sports_sessions';

    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'sports_type',
        'description',
        'date',
        'start_time',
        'end_time',
        'instructor',
        'location',
        'status',
        'notes',
        'required_items',
    ];

    protected $casts = [
        'required_items' => 'array',
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];
}
