<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryStudent extends Model
{
    use HasFactory;

    protected $table = 'library_students';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'admission_number',
        'first_name',
        'last_name',
        'gender',
        'class',
        'stream',
        'library_card_number',
        'phone_number',
        'emergency_contact',
        'clearance_status',
        'clearance_notes',
        'overdue_books',
        'lost_book_balance',
        'joined_date',
        'last_visit',
        'total_visits',
        'total_reading_hours',
        'is_active',
    ];

    protected $casts = [
        'lost_book_balance' => 'decimal:2',
        'overdue_books' => 'integer',
        'total_visits' => 'integer',
        'total_reading_hours' => 'decimal:2',
        'joined_date' => 'date',
        'last_visit' => 'date',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BorrowTransaction::class, 'student_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(LibraryAttendance::class, 'student_id');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(LibraryClearance::class, 'student_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
