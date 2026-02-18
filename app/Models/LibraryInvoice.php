<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryInvoice extends Model
{
    use HasFactory;

    protected $table = 'library_invoices';

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'student_id',
        'student_name',
        'student_class',
        'items',
        'total_amount',
        'amount_paid',
        'balance',
        'status',
        'created_date',
        'due_date',
        'paid_date',
        'cancelled_date',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'created_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'cancelled_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
