<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\DatabaseConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Tenant implements TenantWithDatabase
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'centre_number',
        'district',
        'admin_name',
        'admin_email',
        'admin_phone',
        'status',
        'data',
    ];

    /** Relationships */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function stockMovements()
    {
        return $this->hasManyThrough(StockMovement::class, Item::class);
    }

    /** ----------------------
     * JSON accessors for additional data
     * ----------------------
     */
    public function getDataValue(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /** ----------------------
     * Convenience
     * ----------------------
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the database configuration for this tenant.
     */
    public function database(): DatabaseConfig
    {
        return new DatabaseConfig([
            'database' => 'school_' . $this->id,
        ]);
    }

}
