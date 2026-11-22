<?php

namespace App\Enums;

// Every role in the system, in one place. Use Role::Admin->value instead of typing 'admin'.
enum Role: string
{
    case Student = 'student';
    case Organizer = 'organizer';
    case Admin = 'admin';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
