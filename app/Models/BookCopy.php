<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookCopy extends Model
{
    use HasFactory;

    protected $table = 'book_copies';

    protected $fillable = [
        'tenant_id',
        'title_id',
        'barcode',
        'copy_number',
        'status',
        'condition',
        'location',
        'donated_by',
        'donation_date',
        'acquired_date',
        'last_inspection_date',
        'chain_of_custody',
        'current_holder_id',
        'current_holder_type',
        'last_checkout_date',
        'expected_return_date',
    ];

    protected $casts = [
        'chain_of_custody' => 'array',
        'copy_number' => 'integer',
        'donation_date' => 'date',
        'acquired_date' => 'date',
        'last_inspection_date' => 'date',
        'last_checkout_date' => 'date',
        'expected_return_date' => 'date',
    ];

    public function title(): BelongsTo
    {
        return $this->belongsTo(BookTitle::class, 'title_id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BorrowTransaction::class, 'copy_id');
    }
}
