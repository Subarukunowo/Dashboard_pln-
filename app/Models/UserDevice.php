<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'device_token', 'device_name', 'ip_address', 'last_seen_at'
    ];

    protected $casts = ['last_seen_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}