<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SickbayMedicine extends Model
{
    use BelongsToTenant;

    protected $table = 'sickbay_medicines';

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'dosage',
        'unit',
        'quantity',
        'min_quantity',
        'unit_cost',
        'expiry_date',
        'supplier',
        'storage_location',
        'instructions',
        'side_effects',
        'requires_prescription',
        'is_active',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Scope for active medicines
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for low stock
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    // Scope for expired
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->format('Y-m-d'));
    }

    // Scope for expiring soon
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<', now()->addDays($days)->format('Y-m-d'))
                    ->where('expiry_date', '>=', now()->format('Y-m-d'));
    }

    // Check if medicine is low on stock
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->min_quantity;
    }

    // Check if medicine is expired
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->format('Y-m-d') < now()->format('Y-m-d');
    }
}
