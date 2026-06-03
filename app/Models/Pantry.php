<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Pantry extends Model
{
    use BelongsToTenant;

    protected $table = 'pantries';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'category',
        'location',
        'min_quantity',
        'max_quantity',
        'unit',
        'unit_cost',
        'supplier',
        'supplier_email',
        'supplier_phone',
        'quantity',
        'notes',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'datetime:H:i',
        'is_active' => 'boolean',
        'quantity' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationship with items (through items table with department filter)
    public function items()
    {
        return $this->hasMany(Item::class, 'tenant_id', 'tenant_id');
    }

    // Relationship with transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'tenant_id', 'tenant_id');
    }

    // Relationship with sessions
    public function sessions()
    {
        return $this->hasMany(PantrySession::class, 'tenant_id', 'tenant_id');
    }

    // Relationship with meal plans
    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class, 'tenant_id', 'tenant_id');
    }

    // Get full logo URL
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
