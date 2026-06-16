<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'type',
        'lab_type',
        'store_type',
        'capacity',
        'department',
    ];

    protected $appends = ['current_usage'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function storeItems()
    {
        return $this->hasMany(StoreItem::class);
    }

    public function getCurrentUsageAttribute()
    {
        return $this->items()->count() + $this->storeItems()->count();
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

}
