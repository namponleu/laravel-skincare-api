<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image_url',
        'link',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the banner's image URL
     */
    public function getImageUrlAttribute($value)
    {
        return $value ? $value : asset('images/default-banner.png');
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
