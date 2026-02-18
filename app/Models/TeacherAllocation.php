<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAllocation extends Model
{
    use HasFactory;

    protected $table = 'teacher_allocations';

    protected $fillable = [
        'tenant_id',
        'teacher_id',
        'teacher_name',
        'subject',
        'class_name',
        'book_title_id',
        'copies_allocated',
        'copies_returned',
        'allocation_date',
        'expected_return_date',
        'status',
        'confirmations',
    ];

    protected $casts = [
        'confirmations' => 'array',
        'copies_allocated' => 'integer',
        'copies_returned' => 'integer',
        'allocation_date' => 'date',
        'expected_return_date' => 'date',
    ];

    public function bookTitle(): BelongsTo
    {
        return $this->belongsTo(BookTitle::class, 'book_title_id');
    }
}
