<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryDonor extends Model
{
    use HasFactory;

    protected $table = 'library_donors';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'contact_person',
        'email',
        'phone',
        'address',
        'donation_count',
        'total_books_donated',
        'notes',
    ];

    protected $casts = [
        'donation_count' => 'integer',
        'total_books_donated' => 'integer',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(LibraryDonation::class, 'donor_id');
    }
}
