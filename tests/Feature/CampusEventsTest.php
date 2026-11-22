<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Support\QrToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CampusEventsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $extra = []): User
    {
        $u = User::factory()->create(array_merge([
            'student_number' => fake()->unique()->numerify('2023-#####-CL-0'),
            'course' => 'BSIT', 'year_level' => 3, 'section' => '1',
        ], $extra));
        $u->role = $role;
        $u->save();

        return $u;
    }

    private function event(User $owner): Event
    {
        return Event::create([
            'title' => 'Seminar', 'venue' => 'Gym', 'created_by' => $owner->id,
            'starts_at' => now()->addMinutes(10), 'ends_at' => now()->addHours(2),
            'grace_minutes' => 15, 'target_courses' => [], 'target_year_levels' => [], 'target_sections' => [],
        ]);
    }

    public function test_timezone_is_manila(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
    }

    public function test_organizer_assigned_as_staff_cannot_edit_or_archive_other_event(): void
    {
        $owner = $this->user('organizer');
        $other = $this->user('organizer');
        $event = $this->event($owner);
        $event->staff()->attach($other->id);

        $this->actingAs($other)->get(route('events.edit', $event))->assertForbidden();
        $this->actingAs($other)->delete(route('events.destroy', $event))->assertForbidden();
        $this->actingAs($other)->get(route('events.scan', $event))->assertOk();
    }

    public function test_used_code_stays_blocked_for_its_whole_lifetime(): void
    {
        config(['campus.qr_ttl_seconds' => 600]);
        $owner = $this->user('organizer');
        $student = $this->user('student');
        $event = $this->event($owner);
        $token = QrToken::make($student);

        $this->actingAs($owner)->postJson(route('events.scan.store', $event), ['token' => $token])->assertOk();
        $this->travel(4)->minutes();
        $this->actingAs($owner)->postJson(route('events.scan.store', $event), ['token' => $token])->assertStatus(422);
    }

    public function test_dashboard_loads_for_every_role(): void
    {
        foreach (['student', 'organizer', 'admin'] as $role) {
            $u = $this->user($role);
            $this->event($u);
            $this->actingAs($u)->get('/dashboard')->assertOk();
        }
    }

    public function test_pages_render(): void
    {
        $owner = $this->user('organizer');
        $student = $this->user('student');
        $event = $this->event($owner);
        $this->actingAs($owner)->get(route('events.index'))->assertOk();
        $this->actingAs($owner)->get(route('events.attendees', $event))->assertOk();
        $this->actingAs($owner)->get(route('events.scan', $event))->assertOk();
        $this->actingAs($student)->get(route('my-qr'))->assertOk();
        $this->actingAs($student)->get(route('my-attendance'))->assertOk();
        Auth::logout();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
