<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use App\Models\Cart;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
  protected $fillable = [
      'firstName',
      'lastName',
      'email',
      'password',
      'phone',
      'role_id',
      'role',
      'is_school_admin',
        'tenant_id',
      'accountType',
      'userType',
      'customUserType',
      'customDesignation',
      'bankAccount',
      'mobileMoneyNumber',
      'paymentMethods',
      'department',

  ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     public function cart()
    {
        return $this->hasMany(Cart::class);
    }
     public function role() {
        return $this->belongsTo(Role::class);
        }

        public function movements() {
            return $this->hasMany(StockMovement::class);
        }

        public function school()
        {
            return $this->belongsTo(School::class, 'tenant_id');
        }



}
