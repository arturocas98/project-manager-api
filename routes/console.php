<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', fn () => $this->comment(Inspiring::quote()))
    ->purpose('Display an inspiring quote');

Schedule::command('passport:purge')->hourly();
Schedule::command('telescope:prune --hours=730')->daily();

// Schedule::command(\Spatie\Health\Commands\RunHealthChecksCommand::class)->everyMinute();
// Schedule::command(\Spatie\Health\Commands\DispatchQueueCheckJobsCommand::class)->everyMinute();
// Schedule::command(\Spatie\Health\Commands\ScheduleCheckHeartbeatCommand::class)->everyMinute();
