<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'centre_number',
        'district',
        'county',
        'subcounty',
        'parish',
        'village',
        'admin_name',
        'admin_email',
        'admin_phone',
        'status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Auto-generate UUID on create
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /** Relationships */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
