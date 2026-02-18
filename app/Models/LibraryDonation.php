<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryDonation extends Model
{
    use HasFactory;

    protected $table = 'library_donations';

    protected $fillable = [
        'tenant_id',
        'donor_id',
        'donation_date',
        'type',
        'book_copies',
        'total_books',
        'total_value',
        'condition',
        'purpose',
        'received_by',
        'status',
        'certificate_number',
        'certificate_date',
        'acknowledgement_letter_sent',
        'notes',
    ];

    protected $casts = [
        'book_copies' => 'array',
        'total_books' => 'integer',
        'total_value' => 'decimal:2',
        'donation_date' => 'date',
        'certificate_date' => 'date',
        'acknowledgement_letter_sent' => 'boolean',
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(LibraryDonor::class, 'donor_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
