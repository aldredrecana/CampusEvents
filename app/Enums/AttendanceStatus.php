<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
