<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase3Test extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $u = User::factory()->create([
            'student_number' => fake()->unique()->numerify('2023-#####-CL-0'),
            'course' => 'BSIT', 'year_level' => 3, 'section' => '1',
        ]);
        $u->role = $role;
        $u->save();

        return $u;
    }

    private function event(User $owner, array $extra = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Seminar', 'venue' => 'Gym', 'created_by' => $owner->id,
            'starts_at' => now()->subMinutes(5), 'ends_at' => now()->addHours(2),
            'grace_minutes' => 15, 'target_courses' => [], 'target_year_levels' => [], 'target_sections' => [],
        ], $extra));
    }

    public function test_policy_rules_for_each_role(): void
    {
        $admin = $this->user('admin');
        $owner = $this->user('organizer');
        $otherOrganizer = $this->user('organizer');
        $staff = $this->user('student');
        $student = $this->user('student');
        $event = $this->event($owner);
        $event->staff()->attach($staff->id);

        $this->assertTrue($admin->can('manage', $event));
        $this->assertTrue($owner->can('manage', $event));
        $this->assertFalse($otherOrganizer->can('manage', $event));
        $this->assertFalse($staff->can('manage', $event));

        $this->assertTrue($staff->can('scan', $event));
        $this->assertFalse($student->can('scan', $event));
        $this->assertFalse($otherOrganizer->can('scan', $event));
    }

    public function test_only_owner_can_update_event_through_form_request(): void
    {
        $owner = $this->user('organizer');
        $other = $this->user('organizer');
        $event = $this->event($owner);

        $data = [
            'title' => 'Renamed', 'venue' => 'AVR', 'grace_minutes' => 10,
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'), 'ends_at' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($other)->put(route('events.update', $event), $data)->assertForbidden();
        $this->actingAs($owner)->put(route('events.update', $event), $data)->assertRedirect(route('events.index'));
        $this->assertSame('Renamed', $event->fresh()->title);
    }

    public function test_manual_check_in_accepts_lowercase_student_number(): void
    {
        $owner = $this->user('organizer');
        $student = $this->user('student');
        $event = $this->event($owner);

        $this->actingAs($owner)->post(route('events.manual', $event), [
            'student_number' => ' '.strtolower($student->student_number).' ',
            'note' => 'Phone died',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', ['event_id' => $event->id, 'user_id' => $student->id, 'source' => 'manual']);
    }

    public function test_events_page_does_not_run_one_query_per_event(): void
    {
        $staff = $this->user('student');
        $owner = $this->user('organizer');

        $count = function () use ($staff) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($staff)->get(route('events.index'))->assertOk();

            return count(DB::getQueryLog());
        };

        $this->event($owner)->staff()->attach($staff->id);
        $withOne = $count();

        foreach (range(1, 8) as $i) {
            $this->event($owner, ['title' => "Event {$i}"])->staff()->attach($staff->id);
        }
        $withNine = $count();

        $this->assertSame($withOne, $withNine, 'More events should not mean more queries');
    }

    public function test_qr_code_endpoint_is_rate_limited(): void
    {
        $student = $this->user('student');

        foreach (range(1, 20) as $i) {
            $this->actingAs($student)->getJson(route('my-qr.code'))->assertOk();
        }

        $this->actingAs($student)->getJson(route('my-qr.code'))->assertStatus(429);
    }

    public function test_admin_user_search_works_on_sqlite(): void
    {
        $admin = $this->user('admin');
        User::factory()->create(['name' => 'Jose Peña']);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'peña']))->assertOk()->assertSee('Jose Peña');
    }
}