<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\MasterlistStudent;
use App\Models\User;
use App\Support\QrToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Phase2Test extends TestCase
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
            'starts_at' => now()->subMinutes(5), 'ends_at' => now()->addHours(2),
            'grace_minutes' => 15, 'target_courses' => [], 'target_year_levels' => [], 'target_sections' => [],
        ]);
    }

    private function register(array $overrides = [])
    {
        return $this->post('/register', array_merge([
            'name' => 'New Student',
            'email' => 'new@example.com',
            'student_number' => '2023-00999-CL-0',
            'course' => 'BSIT',
            'year_level' => 2,
            'section' => '2',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides));
    }

    public function test_student_number_must_match_pup_format(): void
    {
        $this->register(['student_number' => '12345'])->assertSessionHasErrors('student_number');
        $this->assertGuest();
    }

    public function test_student_number_is_cleaned_before_saving(): void
    {
        $this->register(['student_number' => ' 2023-00999-cl-0 '])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['student_number' => '2023-00999-CL-0']);
    }

    public function test_masterlist_required_blocks_unknown_numbers_and_uses_official_section(): void
    {
        config(['campus.require_masterlist' => true]);

        $this->register()->assertSessionHasErrors('student_number');

        MasterlistStudent::create([
            'student_number' => '2023-00999-CL-0', 'last_name' => 'Student', 'first_name' => 'New',
            'course' => 'BSIT', 'year_level' => 3, 'section' => '1',
        ]);

        $this->register()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['student_number' => '2023-00999-CL-0', 'year_level' => 3, 'section' => '1']);
    }

    public function test_admin_imports_masterlist_csv_and_syncs_registered_students(): void
    {
        $admin = $this->user('admin');
        $this->user('student', ['student_number' => '2023-00101-CL-0', 'section' => '9']);

        $csv = "\xEF\xBB\xBFStudent No.,Last Name,First Name,Course,Year Level,Section\n"
            ."2023-00101-CL-0,Dela Cruz,Juan,BSIT,3,1\n"
            ."2023-00102-cl-0,Peña,Ana,bsit,3,1\n"
            ."bad-number,X,Y,BSIT,3,1\n"
            ."\n";
        $file = UploadedFile::fake()->createWithContent('list.csv', $csv);

        $this->actingAs($admin)->post(route('admin.masterlist.import'), ['file' => $file, 'sync_users' => 1])
            ->assertRedirect(route('admin.masterlist.index'))
            ->assertSessionHas('import_error_count', 1);

        $this->assertSame(2, MasterlistStudent::count());
        $this->assertDatabaseHas('masterlist_students', ['student_number' => '2023-00102-CL-0', 'last_name' => 'Peña']);
        $this->assertDatabaseHas('users', ['student_number' => '2023-00101-CL-0', 'section' => '1']);

        $this->actingAs($admin)->get(route('admin.masterlist.index'))->assertOk()->assertSee('Peña');
        $this->actingAs($admin)->get(route('admin.masterlist.template'))->assertOk();
    }

    public function test_only_admin_can_open_masterlist(): void
    {
        $this->actingAs($this->user('organizer'))->get(route('admin.masterlist.index'))->assertForbidden();
    }

    public function test_organizer_can_undo_check_in_and_student_can_scan_again(): void
    {
        $owner = $this->user('organizer');
        $student = $this->user('student');
        $event = $this->event($owner);

        $this->actingAs($owner)->postJson(route('events.scan.store', $event), ['token' => QrToken::make($student)])->assertOk();
        $attendance = Attendance::first();

        $this->actingAs($owner)->delete(route('events.attendances.destroy', [$event, $attendance]), [])
            ->assertSessionHasErrors('reason');

        $this->actingAs($owner)->delete(route('events.attendances.destroy', [$event, $attendance]), ['reason' => 'Wrong student'])
            ->assertSessionHas('success');
        $this->assertSame(0, Attendance::count());

        $this->actingAs($owner)->postJson(route('events.scan.store', $event), ['token' => QrToken::make($student)])->assertOk();
        $this->assertSame(1, Attendance::count());
    }

    public function test_gate_staff_cannot_undo_check_in(): void
    {
        $owner = $this->user('organizer');
        $staff = $this->user('student');
        $student = $this->user('student');
        $event = $this->event($owner);
        $event->staff()->attach($staff->id);

        $attendance = Attendance::create([
            'event_id' => $event->id, 'user_id' => $student->id,
            'checked_in_at' => now(), 'status' => 'present', 'source' => 'qr',
        ]);

        $this->actingAs($staff)->delete(route('events.attendances.destroy', [$event, $attendance]), ['reason' => 'x'])
            ->assertForbidden();
        $this->actingAs($staff)->get(route('events.attendees', $event))->assertOk()->assertDontSee('Undo');
        $this->actingAs($owner)->get(route('events.attendees', $event))->assertOk()->assertSee('Undo');
    }
}
