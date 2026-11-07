<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    // super admin punya bypass akses penuh ke semua aksi
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    // izin melihat daftar agenda pemilihan
    public function viewAny(User $user): bool
    {
        return true;
    }

    // izin melihat detail agenda pemilihan
    public function view(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    // izin membuat agenda pemilihan baru
    public function create(User $user): bool
    {
        return true;
    }

    // izin mengubah data agenda pemilihan
    public function update(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    // izin menghapus agenda pemilihan
    public function delete(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    // izin mengelola kandidat, token, dan hasil agenda
    public function manage(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }
}
