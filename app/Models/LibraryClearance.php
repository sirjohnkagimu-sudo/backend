<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryClearance extends Model
{
    use HasFactory;

    protected $table = 'library_clearances';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'student_name',
        'student_class',
        'term',
        'academic_year',
        'status',
        'borrowed_books',
        'total_borrowed',
        'total_returned',
        'total_outstanding',
        'total_lost_book_fees',
        'total_paid',
        'total_balance',
        'cleared_by',
        'cleared_date',
        'blockage_reason',
        'report_card_blocked',
        'qr_code',
        'signature',
    ];

    protected $casts = [
        'borrowed_books' => 'array',
        'total_borrowed' => 'integer',
        'total_returned' => 'integer',
        'total_outstanding' => 'integer',
        'total_lost_book_fees' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_balance' => 'decimal:2',
        'cleared_date' => 'date',
        'report_card_blocked' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }

    public function clearer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
