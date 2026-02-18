<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SickbayVisit extends Model
{
    use BelongsToTenant;

    protected $table = 'sickbay_visits';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'visited_by',
        'visit_date',
        'visit_type',
        'symptoms',
        'diagnosis',
        'treatment',
        'temperature',
        'blood_pressure',
        'pulse',
        'weight',
        'height',
        'medicines_given',
        'notes',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'medicines_given' => 'array',
        'pulse' => 'integer',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    // Relationship with student
    public function student()
    {
        return $this->belongsTo(SickbayStudent::class, 'student_id');
    }

    // Relationship with user who visited
    public function visitedBy()
    {
        return $this->belongsTo(User::class, 'visited_by');
    }

    // Relationship with admissions
    public function admissions()
    {
        return $this->hasMany(SickbayAdmission::class, 'visit_id');
    }

    // Relationship with referrals
    public function referrals()
    {
        return $this->hasMany(SickbayReferral::class, 'visit_id');
    }

    // Scope for filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    // Scope for today's visits
    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', now()->format('Y-m-d'));
    }

    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by visit type
    public function scopeType($query, $type)
    {
        return $query->where('visit_type', $type);
    }
}
