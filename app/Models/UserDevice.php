<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'device_token', 'device_name', 'ip_address',
        'status', 'reject_reason', 'requested_at', 'approved_at',
        'approved_by', 'last_seen_at'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }
    public function isRejected() { return $this->status === 'rejected'; }
}