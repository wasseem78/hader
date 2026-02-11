<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Shift;
use App\Models\User;
use App\Services\Attendance\AttendanceProcessor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceProcessorTest extends TestCase
{
    use RefreshDatabase;

    protected $processor;
    protected $user;
    protected $shift;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new AttendanceProcessor();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        
        // 9:00 AM to 5:00 PM Shift
        $this->shift = Shift::create([
            'company_id' => $this->company->id,
            'name' => 'Regular',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_detects_late_arrival()
    {
        $date = Carbon::today();
        
        // Punch in at 9:20 AM (Late)
        AttendanceRecord::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'punched_at' => $date->copy()->setTime(9, 20),
            'type' => 'in',
        ]);

        $this->processor->process($this->user, $date);

        $record = AttendanceRecord::first();
        $this->assertTrue($record->is_late);
        $this->assertEquals(20, $record->late_minutes);
    }

    public function test_respects_grace_period()
    {
        $date = Carbon::today();
        
        // Punch in at 9:10 AM (Within 15m grace)
        AttendanceRecord::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'punched_at' => $date->copy()->setTime(9, 10),
            'type' => 'in',
        ]);

        $this->processor->process($this->user, $date);

        $record = AttendanceRecord::first();
        $this->assertFalse($record->is_late);
        $this->assertEquals(0, $record->late_minutes);
    }

    public function test_calculates_work_duration_and_overtime()
    {
        $date = Carbon::today();
        
        // 9:00 AM to 6:00 PM (1 hour overtime)
        AttendanceRecord::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'punched_at' => $date->copy()->setTime(9, 0),
            'type' => 'in',
        ]);
        
        AttendanceRecord::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'punched_at' => $date->copy()->setTime(18, 0),
            'type' => 'out',
        ]);

        $this->processor->process($this->user, $date);

        $record = AttendanceRecord::where('type', 'in')->first();
        $this->assertEquals(540, $record->work_duration_minutes); // 9 hours * 60
        $this->assertEquals(60, $record->overtime_minutes); // 1 hour
    }

    public function test_handles_missing_punch_out()
    {
        $date = Carbon::today();
        
        AttendanceRecord::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'punched_at' => $date->copy()->setTime(9, 0),
            'type' => 'in',
        ]);

        $this->processor->process($this->user, $date);

        $record = AttendanceRecord::first();
        $this->assertEquals('missing_punch_out', $record->status);
    }
}
