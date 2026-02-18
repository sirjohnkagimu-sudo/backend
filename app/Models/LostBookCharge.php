<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostBookCharge extends Model
{
    use HasFactory;

    protected $table = 'lost_book_charges';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'copy_id',
        'book_title',
        'replacement_cost',
        'amount_paid',
        'balance',
        'payment_status',
        'invoice_number',
        'created_date',
        'paid_date',
        'payment_method',
        'receipt_number',
    ];

    protected $casts = [
        'replacement_cost' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'created_date' => 'date',
        'paid_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'copy_id');
    }
}
