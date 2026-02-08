<?php

namespace App\Console\Commands;

use App\Support\ConferenceSignalingServerSupervisor;
use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class ServeCommand extends BaseServeCommand
{
    protected $description = 'Serve the application and auto-run conference signaling WebSocket server';

    public function handle()
    {
        $process = ConferenceSignalingServerSupervisor::start();

        if ($process !== null) {
            $host = (string) config('conference_signaling.server.bind_host');
            $port = (int) config('conference_signaling.server.port');
            $this->components->info("Conference signaling WS running on [ws://{$host}:{$port}].");
        }

        try {
            return parent::handle();
        } finally {
            ConferenceSignalingServerSupervisor::stop();
            $this->components->info('Conference signaling WS stopped.');
        }
    }
}
