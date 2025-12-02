<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\School;
use App\Models\Timetable;
use App\Models\User;
use App\Models\LoginKey;
use App\Models\TimetableGroup;
use App\Models\Subject;
use App\Models\TimetableTeacherWorkingDay;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests for Teacher Working Days functionality
 * 
 * To run these tests:
 * php artisan test --filter=TeacherWorkingDaysTest
 */
class TeacherWorkingDaysTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Timetable $timetable;
    private User $admin;
    private LoginKey $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        // Create school and admin user
        $this->school = School::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->school->users()->attach($this->admin->id, ['is_admin' => true]);

        // Create timetable
        $this->timetable = Timetable::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Test Timetable',
        ]);

        // Create teacher
        $this->teacher = LoginKey::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'teacher',
            'full_name' => 'Test Teacher',
        ]);
    }

    /** @test */
    public function it_can_set_teacher_working_days()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('schools.timetables.update-teacher-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
        ]), [
            'teacher_id' => $this->teacher->id,
            'working_days' => [1, 3, 5], // Monday, Wednesday, Friday
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Verify in database
        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 1,
        ]);

        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 3,
        ]);

        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 5,
        ]);

        // Tuesday (2) and Thursday (4) should NOT exist
        $this->assertDatabaseMissing('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 2,
        ]);

        $this->assertDatabaseMissing('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 4,
        ]);
    }

    /** @test */
    public function it_can_get_teacher_working_days()
    {
        $this->actingAs($this->admin);

        // Set working days
        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 1,
        ]);

        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 3,
        ]);

        $response = $this->getJson(route('schools.timetables.teacher-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
            'teacher_id' => $this->teacher->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'teacher_id' => $this->teacher->id,
            'working_days' => [1, 3],
        ]);
    }

    /** @test */
    public function it_can_get_all_teachers_working_days()
    {
        $this->actingAs($this->admin);

        // Create a subject and group to link the teacher
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);
        
        TimetableGroup::create([
            'timetable_id' => $this->timetable->id,
            'name' => 'Test Group',
            'subject_id' => $subject->id,
            'teacher_login_key_id' => $this->teacher->id,
            'lessons_per_week' => 5,
        ]);

        // Set working days
        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 2,
        ]);

        $response = $this->getJson(route('schools.timetables.all-teachers-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1); // One teacher
        $response->assertJsonFragment([
            'teacher_id' => $this->teacher->id,
            'teacher_name' => 'Test Teacher',
            'working_days' => [2],
        ]);
    }

    /** @test */
    public function it_returns_all_days_if_no_working_days_set()
    {
        // Default behavior: if no working days set, teacher works all days
        $workingDays = $this->timetable->getTeacherWorkingDays($this->teacher->id);
        
        $this->assertEmpty($workingDays); // Returns empty array

        // But isTeacherWorkingOnDay should return true for all days
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 1));
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 2));
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 3));
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 4));
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 5));
    }

    /** @test */
    public function it_checks_if_teacher_is_working_on_specific_day()
    {
        // Set teacher to work only on Monday (1) and Friday (5)
        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 1,
        ]);

        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 5,
        ]);

        // Monday - works
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 1));
        
        // Tuesday - doesn't work
        $this->assertFalse($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 2));
        
        // Wednesday - doesn't work
        $this->assertFalse($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 3));
        
        // Thursday - doesn't work
        $this->assertFalse($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 4));
        
        // Friday - works
        $this->assertTrue($this->timetable->isTeacherWorkingOnDay($this->teacher->id, 5));
    }

    /** @test */
    public function it_can_update_existing_working_days()
    {
        $this->actingAs($this->admin);

        // Set initial working days (Monday, Wednesday)
        $this->postJson(route('schools.timetables.update-teacher-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
        ]), [
            'teacher_id' => $this->teacher->id,
            'working_days' => [1, 3],
        ]);

        // Update to different days (Tuesday, Thursday, Friday)
        $response = $this->postJson(route('schools.timetables.update-teacher-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
        ]), [
            'teacher_id' => $this->teacher->id,
            'working_days' => [2, 4, 5],
        ]);

        $response->assertOk();

        // Old days should be removed
        $this->assertDatabaseMissing('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 1,
        ]);

        $this->assertDatabaseMissing('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 3,
        ]);

        // New days should exist
        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 2,
        ]);

        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 4,
        ]);

        $this->assertDatabaseHas('timetable_teacher_working_days', [
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 5,
        ]);
    }

    /** @test */
    public function it_validates_working_days_input()
    {
        $this->actingAs($this->admin);

        // Invalid day number (6 - Saturday, should be 1-5)
        $response = $this->postJson(route('schools.timetables.update-teacher-working-days', [
            'school' => $this->school,
            'timetable' => $this->timetable,
        ]), [
            'teacher_id' => $this->teacher->id,
            'working_days' => [1, 2, 6],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function different_timetables_can_have_different_working_days_for_same_teacher()
    {
        // Create another timetable
        $timetable2 = Timetable::factory()->create([
            'school_id' => $this->school->id,
            'name' => 'Second Timetable',
        ]);

        // Set working days for first timetable (Monday, Wednesday)
        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 1,
        ]);

        TimetableTeacherWorkingDay::create([
            'timetable_id' => $this->timetable->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 3,
        ]);

        // Set different working days for second timetable (Tuesday, Thursday, Friday)
        TimetableTeacherWorkingDay::create([
            'timetable_id' => $timetable2->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 2,
        ]);

        TimetableTeacherWorkingDay::create([
            'timetable_id' => $timetable2->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 4,
        ]);

        TimetableTeacherWorkingDay::create([
            'timetable_id' => $timetable2->id,
            'teacher_login_key_id' => $this->teacher->id,
            'day_of_week' => 5,
        ]);

        // Verify first timetable
        $workingDays1 = $this->timetable->getTeacherWorkingDays($this->teacher->id);
        $this->assertEquals([1, 3], $workingDays1);

        // Verify second timetable
        $workingDays2 = $timetable2->getTeacherWorkingDays($this->teacher->id);
        $this->assertEquals([2, 4, 5], $workingDays2);
    }
}
