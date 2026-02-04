<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PantrySession extends Model
{
    use BelongsToTenant;

    protected $table = 'pantry_sessions';

    protected $fillable = [
        'tenant_id',
        'pantry_id',
        'title',
        'type',
        'description',
        'date',
        'start_time',
        'end_time',
        'expected_pax',
        'actual_pax',
        'instructor',
        'required_items',
        'status',
        'notes',
        'menu',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'expected_pax' => 'integer',
        'actual_pax' => 'integer',
        'required_items' => 'array',
        'status' => 'string',
    ];

    protected $attributes = [
        'status' => 'planned',
    ];

    // Relationship with pantry
    public function pantry()
    {
        return $this->belongsTo(Pantry::class, 'pantry_id');
    }

    // Relationship with session items (consumed during session)
    public function consumedItems()
    {
        return $this->hasMany(PantrySessionItem::class);
    }
}
