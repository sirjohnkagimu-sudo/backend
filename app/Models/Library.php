<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Library extends Model
{
    use HasFactory;
    protected $table = 'libraries';

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
        'images' => 'array', // Casts the JSON field to an array
    ];
    protected $attributes = [
        'images' => '[]', // Default value for images
    ];
    protected $appends = ['images_array', 'avatar_url', 'images_url'];


    public function getImagesArrayAttribute()
    {
        return json_decode($this->images, true);
    }
    public function getAvatarUrlAttribute()
    {
        return url('storage/' . $this->avatar);
    }
    public function getImagesUrlAttribute()
    {
        return array_map(function ($image) {
            return url('storage/' . $image);
        }, $this->images ?? []);
    }

    /**
     * Get the location that the library item belongs to
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
