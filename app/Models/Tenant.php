<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $fillable = ['id', 'name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
