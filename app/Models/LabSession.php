<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',
        'title',
        'type',
        'lab_type',
        'description',
        'notes',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'students',
        'instructor',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
