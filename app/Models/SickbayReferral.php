<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class SickbayReferral extends Model
{
    use BelongsToTenant;

    protected $table = 'sickbay_referrals';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'visit_id',
        'admission_id',
        'referred_by',
        'referral_date',
        'facility_name',
        'facility_contact',
        'facility_address',
        'department',
        'reason',
        'clinical_notes',
        'treatment_given',
        'urgency',
        'status',
        'follow_up_date',
        'follow_up_notes',
    ];

    protected $casts = [
        'referral_date' => 'datetime',
        'follow_up_date' => 'datetime',
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

    // Relationship with admission
    public function admission()
    {
        return $this->belongsTo(SickbayAdmission::class, 'admission_id');
    }

    // Relationship with user who referred
    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    // Scope for pending referrals
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for completed referrals
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope for emergency referrals
    public function scopeEmergency($query)
    {
        return $query->where('urgency', 'emergency');
    }

    // Scope for filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('referral_date', [$startDate, $endDate]);
    }

    // Check if referral is pending
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    // Check if referral is emergency
    public function getIsEmergencyAttribute()
    {
        return $this->urgency === 'emergency';
    }
}
