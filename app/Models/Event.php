<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title', 'description', 'venue', 'starts_at', 'ends_at', 'created_by',
        'target_courses', 'target_year_levels', 'target_sections',
        'requires_checkout', 'grace_minutes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'target_courses' => 'array',
        'target_year_levels' => 'array',
        'target_sections' => 'array',
        'requires_checkout' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function opensAt()
    {
        return $this->starts_at->copy()->subMinutes(config('campus.checkin_opens_minutes_before'));
    }

    public function isScanOpen(): bool
    {
        return now()->between($this->opensAt(), $this->ends_at);
    }

    public function isEligible(User $user): bool
    {
        if ($user->role !== 'student') {
            return false;
        }

        $rules = [
            'target_courses' => 'course',
            'target_year_levels' => 'year_level',
            'target_sections' => 'section',
        ];

        foreach ($rules as $field => $attribute) {
            $allowed = $this->{$field};
            if (! empty($allowed) && ! in_array($user->{$attribute}, $allowed)) {
                return false;
            }
        }

        return true;
    }

    public function staff()
{
    return $this->belongsToMany(User::class, 'event_staff')->withTimestamps();
}

public function canBeManagedBy(User $user): bool
{
    return $user->role === 'admin'
        || ($user->role === 'organizer' && $this->created_by === $user->id);
}

public function canBeScannedBy(User $user): bool
{
    return $this->canBeManagedBy($user)
        || $this->staff()->where('users.id', $user->id)->exists();
}

    // Everyone expected at this event (used for absent lists in Phase 4)
    public function eligibleStudents()
{
    return User::where('role', 'student')
        ->where('created_at', '<=', $this->ends_at)
        ->when($this->target_courses, fn ($q, $v) => $q->whereIn('course', $v))
        ->when($this->target_year_levels, fn ($q, $v) => $q->whereIn('year_level', $v))
        ->when($this->target_sections, fn ($q, $v) => $q->whereIn('section', $v));
}

    public function scopeVisibleTo($query, User $user)
{
    if ($user->role !== 'student') {
        return $query;
    }

    $fields = [
        'target_courses' => $user->course,
        'target_year_levels' => $user->year_level,
        'target_sections' => $user->section,
    ];

    foreach ($fields as $column => $value) {
        $query->where(function ($q) use ($column, $value) {
            $q->whereNull($column)->orWhereJsonLength($column, 0);

            if ($value !== null) {
                $q->orWhereJsonContains($column, $value);
            }
        });
    }

    return $query;
}
}