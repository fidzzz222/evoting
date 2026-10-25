<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'user_name',
        'user_role',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Voter::class, 'user_id');
    }

    public function getActorNameAttribute(): string
    {
        if ($this->user_name) {
            return $this->user_name;
        }

        if ($this->user_type === 'VOTER' && $this->voter) {
            return $this->voter->nama;
        }

        if ($this->user_type === 'USER' && $this->user) {
            return $this->user->nama;
        }

        return 'Sistem / Anonim';
    }

    public function getActorRoleAttribute(): string
    {
        if ($this->user_role) {
            return $this->user_role;
        }

        if ($this->user_type === 'VOTER' && $this->voter) {
            return $this->voter->type;
        }

        if ($this->user_type === 'USER' && $this->user) {
            return $this->user->role;
        }

        return 'SYSTEM';
    }

    public static function log(string $action, ?string $description = null, ?array $payload = null, $user = null): self
    {
        $req = request();
        $currentUser = $user ?? ($req ? $req->user() : null) ?? Auth::guard('web')->user() ?? Auth::guard('voter')->user();

        $userType = null;
        $userId = null;
        $userName = null;
        $userRole = null;

        if ($currentUser) {
            if ($currentUser instanceof User) {
                $userType = 'USER';
                $userId = $currentUser->id;
                $userName = $currentUser->nama;
                $userRole = $currentUser->role;
            } elseif ($currentUser instanceof Voter) {
                $userType = 'VOTER';
                $userId = $currentUser->id;
                $userName = $currentUser->nama;
                $userRole = $currentUser->type;
            }
        }

        return self::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => $action,
            'description' => $description,
            'ip_address' => $req ? $req->ip() : null,
            'user_agent' => $req ? substr($req->userAgent() ?? '', 0, 255) : null,
            'payload' => $payload,
        ]);
    }
}
