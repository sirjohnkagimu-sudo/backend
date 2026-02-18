<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryAttendance extends Model
{
    use HasFactory;

    protected $table = 'library_attendance';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'student_name',
        'student_class',
        'entry_time',
        'exit_time',
        'purpose',
        'reading_hours',
        'books_read',
        'qr_code_scanned',
        'gate',
    ];

    protected $casts = [
        'entry_time' => 'datetime:H:i',
        'exit_time' => 'datetime:H:i',
        'reading_hours' => 'decimal:2',
        'books_read' => 'integer',
        'qr_code_scanned' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(LibraryStudent::class, 'student_id');
    }
}
