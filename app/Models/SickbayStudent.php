<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SickbayStudent extends Model
{
    use BelongsToTenant;

    protected $table = 'sickbay_students';

    protected $fillable = [
        'tenant_id',
        'admission_number',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'class',
        'stream',
        'parent_name',
        'parent_phone',
        'parent_email',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'emergency_contact',
        'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'custom_fields' => 'array',
    ];

    // Relationship with visits
    public function visits()
    {
        return $this->hasMany(SickbayVisit::class, 'student_id');
    }

    // Relationship with admissions
    public function admissions()
    {
        return $this->hasMany(SickbayAdmission::class, 'student_id');
    }

    // Relationship with referrals
    public function referrals()
    {
        return $this->hasMany(SickbayReferral::class, 'student_id');
    }

    // Get full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Scope for active students
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for filtering by class
    public function scopeClass($query, $class)
    {
        return $query->where('class', $class);
    }
}
