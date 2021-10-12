<?php

namespace Tests\Unit;

use Tests\TestCase;

class ScheduleRunTest extends TestCase
{
    public function test_schedule_run()
    {
        $this->artisan('schedule:run')->assertExitCode(0);
    }
}
