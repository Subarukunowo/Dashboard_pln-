<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
      'name', 'email', 'password', 'google_id', 'avatar',
        'two_factor_secret', 'two_factor_enabled', 'is_active'
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
    public function getAvatarUrlAttribute(): ?string
    {
        // 1. Kolom 'avatar' sudah berisi URL lengkap (Google OAuth)
        if (!empty($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }
 
        // 2. Jetstream / custom path (opsional — hapus jika tidak pakai)
        // if (!empty($this->profile_photo_path)) {
        //     return Storage::url($this->profile_photo_path);
        // }
 
        return null;
    }
 
    /**
     * Helper: apakah user login via Google OAuth?
     */
    public function isGoogleUser(): bool
    {
        return !empty($this->google_id);
    }
       public function devices()      { return $this->hasMany(UserDevice::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
}
