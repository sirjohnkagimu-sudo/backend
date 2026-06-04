<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreItem extends Model
{
    use HasFactory;

    protected $table = 'store_items';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'category',
        'avatar',
        'images',
        'color',
        'brand',
        'in_stock',
        'min_quantity',
        'condition',
        'price',
        'discount',
        'desc',
        'location_id',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    protected $attributes = [
        'category' => 'office',
        'condition' => 'new',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeByTenant($query)
    {
        return $query->where('tenant_id', auth()->user()?->tenant_id);
    }
}
