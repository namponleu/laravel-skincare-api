<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activity extends Model
{
    protected $fillable = [
        'type',
        'description',
        'user_type',
        'username',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get recent activities for dashboard (admin only, no orders)
     */
    public static function getRecent($limit = 1)
    {
        return self::where('user_type', 'admin')
                   ->whereNotIn('type', ['order_created', 'order_updated', 'order_deleted'])
                   ->latest()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get recent activities for dashboard (all types)
     */
    public static function getAllRecent($limit = 10)
    {
        return self::latest()->limit($limit)->get();
    }

    /**
     * Log an activity
     */
    public static function log($type, $description, $username = null, $userType = null, $metadata = null)
    {
        return self::create([
            'type' => $type,
            'description' => $description,
            'username' => $username,
            'user_type' => $userType,
            'ip_address' => request()->ip(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get activity color based on type
     */
    public function getColorAttribute()
    {
        $colors = [
            'login' => 'green',
            'logout' => 'red',
            'user_created' => 'blue',
            'user_updated' => 'yellow',
            'user_deleted' => 'red',
            'order_created' => 'purple',
            'order_updated' => 'orange',
            'order_deleted' => 'red',
            'product_created' => 'blue',
            'product_updated' => 'yellow',
            'product_deleted' => 'red',
        ];

        return $colors[$this->type] ?? 'gray';
    }
}
