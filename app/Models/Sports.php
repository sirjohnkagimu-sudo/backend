<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Sports extends Model
{
    use HasFactory;
    protected $table = 'sports';
    protected $fillable = [
        'name',
        'category',
        'avatar',
        'images',
        'color',
        'brand',
        'in_stock',
        'min_quantity',
        'condition',
        'price',
        'discount',
        'desc',
        'location_id',
    ];

    protected $casts = [
        'images' => 'array', // Cast images to array
    ];
    protected $attributes = [
        'condition' => 'new', // Default condition
    ];
    protected $appends = [
        'avatar_url',
        'images_url',
    ];
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
    public function getImagesUrlAttribute()
    {
        $images = is_array($this->images) ? $this->images : json_decode($this->images, true) ?? [];
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $images);
    }

}
