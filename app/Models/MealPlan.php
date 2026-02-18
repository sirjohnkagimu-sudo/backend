<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MealPlan extends Model
{
    use BelongsToTenant;

    protected $table = 'meal_plans';

    protected $fillable = [
        'tenant_id',
        'pantry_id',
        'day',
        'date',
        'breakfast',
        'lunch',
        'dinner',
        'snacks',
        'prepared_by',
        'approved_by',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'breakfast' => 'array',
        'lunch' => 'array',
        'dinner' => 'array',
        'snacks' => 'array',
        'prepared_by' => 'integer',
        'approved_by' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationship with pantry
    public function pantry()
    {
        return $this->belongsTo(Pantry::class);
    }

    // Relationship with user who prepared the meal plan
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // Relationship with user who approved the meal plan
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by date
    public function scopeDate($query, $date)
    {
        return $query->where('date', $date);
    }

    // Scope for filtering by pantry
    public function scopePantry($query, $pantryId)
    {
        return $query->where('pantry_id', $pantryId);
    }
}
