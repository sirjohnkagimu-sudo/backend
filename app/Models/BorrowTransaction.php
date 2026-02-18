<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowTransaction extends Model
{
    use HasFactory;

    protected $table = 'borrow_transactions';

    protected $fillable = [
        'tenant_id',
        'copy_id',
        'student_id',
        'issued_by',
        'issued_date',
        'due_date',
        'return_date',
        'term',
        'academic_year',
        'status',
        'condition_on_return',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'copy_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
