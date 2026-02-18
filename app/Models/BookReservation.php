<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReservation extends Model
{
    use HasFactory;

    protected $table = 'book_reservations';

    protected $fillable = [
        'tenant_id',
        'requester_id',
        'requester_type',
        'requester_name',
        'subject',
        'topic',
        'book_title_id',
        'class_name',
        'number_of_copies',
        'purpose',
        'requested_date',
        'required_date',
        'status',
        'approved_by',
        'approved_date',
        'rejection_reason',
        'fulfilled_date',
        'notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'required_date' => 'date',
        'approved_date' => 'date',
        'fulfilled_date' => 'date',
        'number_of_copies' => 'integer',
    ];

    public function bookTitle(): BelongsTo
    {
        return $this->belongsTo(BookTitle::class, 'book_title_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
