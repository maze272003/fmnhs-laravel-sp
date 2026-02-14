<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('assignments:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

Schedule::command('assignments:send-reminders')
    ->dailyAt('18:00')
    ->withoutOverlapping();

Schedule::command('students:detect-at-risk')
    ->weekly()
    ->mondays()
    ->at('06:00')
    ->withoutOverlapping();

Schedule::command('reports:generate-progress --period=weekly')
    ->weekly()
    ->fridays()
    ->at('20:00')
    ->withoutOverlapping();
