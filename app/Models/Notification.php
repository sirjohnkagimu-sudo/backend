<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'details',
        'is_read',
        'is_ignored',
        'timestamp',
        'priority',
        'action_url',
        'related_item'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_ignored' => 'boolean',
        'timestamp' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
