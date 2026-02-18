<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LibrarySettings extends Model
{
    use HasFactory;

    protected $table = 'library_settings';

    protected $fillable = [
        'tenant_id',
        'academic_year',
        'current_term',
        'term_dates',
        'borrowing_rules',
        'clearance_settings',
        'contact_info',
    ];

    protected $casts = [
        'term_dates' => 'array',
        'borrowing_rules' => 'array',
        'clearance_settings' => 'array',
        'contact_info' => 'array',
    ];
}
