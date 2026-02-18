<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookTitle extends Model
{
    use HasFactory;

    protected $table = 'book_titles';

    protected $fillable = [
        'tenant_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'year',
        'subject',
        'class',
        'category',
        'replacement_cost',
        'total_copies',
    ];

    protected $casts = [
        'replacement_cost' => 'decimal:2',
        'year' => 'integer',
        'total_copies' => 'integer',
    ];

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class, 'title_id');
    }

    public function teacherAllocations(): HasMany
    {
        return $this->hasMany(TeacherAllocation::class, 'book_title_id');
    }

    public function bulkIssues(): HasMany
    {
        return $this->hasMany(BulkIssue::class, 'book_title_id');
    }
}
