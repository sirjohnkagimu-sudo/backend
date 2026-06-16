<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'item_id',
        'tenant_id',
        'type',
        'quantity',
        'reason',
        'approved_by',
        'created_by',
        'update_history',
        'notes',
        'cost',
    ];

    protected $casts = [
        'update_history' => 'array',
        'cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function storeItem()
    {
        return $this->belongsTo(StoreItem::class, 'item_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'tenant_id');
    }
}
