<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Voter extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'voters';

    protected $fillable = [
        'identifier',
        'nama',
        'kelas',
        'type',
        'is_active',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isSiswa(): bool
    {
        return $this->type === 'SISWA';
    }

    public function isGuru(): bool
    {
        return $this->type === 'GURU';
    }

    public function eventParticipations()
    {
        return $this->hasMany(EventParticipation::class, 'voter_id');
    }

    public function participations()
    {
        return $this->eventParticipations();
    }

    public function getNisnAttribute(): string
    {
        return (string) $this->identifier;
    }

    public function getNameAttribute(): string
    {
        return (string) $this->nama;
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->type === 'GURU' ? 'Dewan Guru / Staff' : 'Siswa / Pemilih DPT';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'GURU' ? 'Guru / Staff' : 'Siswa';
    }

    public function getVoterTypeAttribute(): string
    {
        return (string) $this->type;
    }
}
