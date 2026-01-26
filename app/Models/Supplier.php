<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Supplier extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'contact',
        'phone',
        'email',
        'website',
        'address',
        'contact_person',
        'is_active',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
