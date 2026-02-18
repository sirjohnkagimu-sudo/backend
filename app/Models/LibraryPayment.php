<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryPayment extends Model
{
    use HasFactory;

    protected $table = 'library_payments';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'student_name',
        'type',
        'reference_type',
        'reference_id',
        'amount',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'collected_by',
        'collection_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collection_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
