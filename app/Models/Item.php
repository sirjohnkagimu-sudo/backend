<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Item extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'category_id',
        'supplier_id',
        'location_id',
        'quantity',
        'min_quantity',
        'max_quantity',
        'expiry_date',
        'unit',
        'unit_cost',
        'total_value',
        'department',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function location() {
        return $this->belongsTo(Location::class);
    }

    public function stockMovements() {
        return $this->hasMany(StockMovement::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
