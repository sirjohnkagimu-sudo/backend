<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SickbayAdmission extends Model
{
    use BelongsToTenant;

    protected $table = 'sickbay_admissions';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'visit_id',
        'admitted_by',
        'admission_date',
        'discharge_date',
        'bed_number',
        'ward',
        'reason',
        'diagnosis',
        'treatment_plan',
        'daily_notes',
        'status',
        'discharge_notes',
        'follow_up_instructions',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];

    // Relationship with student
    public function student()
    {
        return $this->belongsTo(SickbayStudent::class, 'student_id');
    }

    // Relationship with visit
    public function visit()
    {
        return $this->belongsTo(SickbayVisit::class, 'visit_id');
    }

    // Relationship with user who admitted
    public function admittedBy()
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    // Relationship with referrals
    public function referrals()
    {
        return $this->hasMany(SickbayReferral::class, 'admission_id');
    }

    // Scope for currently admitted patients
    public function scopeAdmitted($query)
    {
        return $query->where('status', 'admitted');
    }

    // Scope for discharged patients
    public function scopeDischarged($query)
    {
        return $query->where('status', 'discharged');
    }

    // Scope for filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('admission_date', [$startDate, $endDate]);
    }

    // Check if patient is still admitted
    public function getIsAdmittedAttribute()
    {
        return $this->status === 'admitted';
    }

    // Calculate stay duration in days
    public function getStayDurationAttribute()
    {
        $discharge = $this->discharge_date ?? now();
        return $this->admission_date->diffInDays($discharge) + 1;
    }
}
