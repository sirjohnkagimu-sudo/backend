<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionUpdateHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_transaction_id',
        'updated_by',
        'previous_values',
        'new_values',
        'update_reason',
        'updated_at',
    ];

    protected $casts = [
        'previous_values' => 'array',
        'new_values' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(StockMovement::class, 'inventory_transaction_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
