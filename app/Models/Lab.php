<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lab extends Model
{
    use HasFactory;

    protected $table = 'labs';

    protected $fillable = [
        'name',
        'category',
        'subcategory',
        'avatar',
        'images',
        'color',
        'rating',
        'in_stock',
        'condition',
        'price',
        'unit',
        'desc',
        "purchaseType",
        'expiry_date',
    ];

    protected $casts = [
        'images' => 'array',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'images' => '[]',
    ];

    protected $appends = [ 'avatar_url', 'images_url', 'is_expiring_soon', 'days_until_expiry', 'is_expired' ];

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }

        // If avatar is a full URL (e.g. Imgur), return it as is
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
    }

    public function getImagesUrlAttribute()
    {
        $images = $this->images;

        // Handle case where images might be stored as JSON string
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }

        return array_map(function ($image) {
            // If the image path is a full URL, return it directly
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            // Otherwise, build the storage asset URL
            return asset('storage/' . $image);
        }, $images ?? []);
    }

    /**
     * Check if product is expiring within 30 days
     */
    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }

        $today = now()->startOfDay();
        $expiryDate = $this->expiry_date->startOfDay();
        $daysUntilExpiry = $today->diffInDays($expiryDate, false);

        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= 30;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        $today = now()->startOfDay();
        $expiryDate = $this->expiry_date->startOfDay();

        return $today->diffInDays($expiryDate, false);
    }

    /**
     * Check if product is expired
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->gt($this->expiry_date);
    }
}
