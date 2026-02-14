<?php

namespace App\Providers;

use App\Events\ConferenceJoined;
use App\Listeners\AutoMarkAttendance;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ConferenceJoined::class => [
            AutoMarkAttendance::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
