<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterlistStudent extends Model
{
    protected $fillable = [
        'student_number', 'last_name', 'first_name', 'course', 'year_level', 'section',
    ];

    // The registered account that uses this student number (if any)
    public function user()
    {
        return $this->hasOne(User::class, 'student_number', 'student_number');
    }
}
