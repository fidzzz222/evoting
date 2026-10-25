<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPER_ADMIN';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'PETUGAS';
    }

    public function getNameAttribute(): string
    {
        return (string) $this->nama;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'SUPER_ADMIN' => 'Super Administrator',
            'PETUGAS' => 'Panitia / Petugas Pemilihan',
            default => (string) ($this->role ?? 'Petugas'),
        };
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    public function assignedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_officers', 'user_id', 'event_id')->withTimestamps();
    }
}
