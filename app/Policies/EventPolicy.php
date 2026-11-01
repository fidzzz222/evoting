<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admin can perform any action.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }

    /**
     * Determine whether the user can manage the event and its resources.
     */
    public function manage(User $user, Event $event): bool
    {
        return $event->canBeManagedBy($user);
    }
}
