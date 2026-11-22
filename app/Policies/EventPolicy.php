<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Event;
use App\Models\User;

// All "who can do what" rules for events live here.
// Laravel finds this file automatically because it is named after the Event model.
class EventPolicy
{
    // Admins can do everything, so the other checks are skipped for them
    public function before(User $user): ?bool
    {
        return $user->role === Role::Admin->value ? true : null;
    }

    // Edit, archive, restore, assign staff, export, undo check-ins
    public function manage(User $user, Event $event): bool
    {
        return $user->role === Role::Organizer->value
            && (int) $event->created_by === (int) $user->id;
    }

    // Open the scanner, scan, manual check-in, view attendees
    public function scan(User $user, Event $event): bool
    {
        if ($this->manage($user, $event)) {
            return true;
        }

        // If the page already loaded this user's staff assignments, use them (no extra query)
        if ($event->relationLoaded('staff')) {
            return $event->staff->contains('id', $user->id);
        }

        return $event->staff()->where('users.id', $user->id)->exists();
    }
}
