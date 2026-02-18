<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkIssue extends Model
{
    use HasFactory;

    protected $table = 'bulk_issues';

    protected $fillable = [
        'tenant_id',
        'book_title_id',
        'class',
        'term',
        'academic_year',
        'issue_date',
        'due_date',
        'issued_by',
        'status',
        'total_copies',
        'issued_copies',
        'pending_copies',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_copies' => 'integer',
        'issued_copies' => 'integer',
        'pending_copies' => 'integer',
    ];

    public function bookTitle(): BelongsTo
    {
        return $this->belongsTo(BookTitle::class, 'book_title_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
