<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for presentations. Safe to run more than once:
     * it updates the same accounts and events instead of making duplicates.
     * Every demo account uses the password: password
     */
    public function run(): void
    {
        $admin = $this->account('Admin Demo', 'admin@demo.test', 'admin');
        $organizer = $this->account('Maria Santos', 'organizer@demo.test', 'organizer');

        $students = collect([
            ['Juan Dela Cruz', '2023-00101-CL-0', 3, '1'],
            ['Ana Reyes', '2023-00102-CL-0', 3, '1'],
            ['Jose Peña', '2023-00103-CL-0', 3, '2'],
            ['Liza Bautista', '2024-00104-CL-0', 2, '1'],
            ['Mark Villanueva', '2024-00105-CL-0', 2, '1'],
            ['Carla Mendoza', '2025-00106-CL-0', 1, '1'],
        ])->map(fn ($s, $i) => $this->account($s[0], 'student'.($i + 1).'@demo.test', 'student', [
            'student_number' => $s[1],
            'course' => 'BSIT',
            'year_level' => $s[2],
            'section' => $s[3],
        ]));

        // 1. Happening now: open for scanning during the demo
        $live = $this->event($organizer, 'IT Week Opening Seminar', 'PUP Calauan Gymnasium',
            now()->subMinutes(20), now()->addHours(3));

        // 2. Upcoming
        $this->event($organizer, 'Org General Assembly', 'AVR',
            now()->addDays(3)->setTime(13, 0), now()->addDays(3)->setTime(16, 0));

        // 3. Already ended: shows present, late, and absent students in reports
        $past = $this->event($organizer, 'Cybersecurity Awareness Talk', 'Room 201',
            now()->subDays(2)->setTime(9, 0), now()->subDays(2)->setTime(11, 0), ['target_year_levels' => [3]]);

        $this->attend($past, $students[0], $organizer, 'present', 5);
        $this->attend($past, $students[1], $organizer, 'late', 25);
        // students[2] did not attend, so they show as Absent

        $this->attend($live, $students[0], $organizer, 'present', -15);

        // Juan is gate staff for the live event (shows on his dashboard)
        $live->staff()->syncWithoutDetaching([$students[0]->id]);

        $this->command?->info('Demo data ready. Log in with admin@demo.test, organizer@demo.test, or student1@demo.test (password: password)');
    }

    private function account(string $name, string $email, string $role, array $extra = []): User
    {
        $user = User::updateOrCreate(['email' => $email], array_merge([
            'name' => $name,
            'password' => 'password',
        ], $extra));

        // role and email_verified_at are not mass-assignable, so they are set directly
        $user->role = $role;
        $user->email_verified_at ??= now();
        $user->save();

        return $user;
    }

    private function event(User $owner, string $title, string $venue, $starts, $ends, array $audience = []): Event
    {
        return Event::updateOrCreate(['title' => $title], array_merge([
            'venue' => $venue,
            'description' => 'Demo event',
            'starts_at' => $starts,
            'ends_at' => $ends,
            'created_by' => $owner->id,
            'grace_minutes' => 15,
            'requires_checkout' => false,
            'target_courses' => [],
            'target_year_levels' => [],
            'target_sections' => [],
        ], $audience));
    }

    private function attend(Event $event, User $student, User $scanner, string $status, int $minutesAfterStart): void
    {
        Attendance::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $student->id],
            [
                'checked_in_at' => $event->starts_at->copy()->addMinutes($minutesAfterStart),
                'status' => $status,
                'scanned_by' => $scanner->id,
                'source' => 'qr',
            ],
        );
    }
}
