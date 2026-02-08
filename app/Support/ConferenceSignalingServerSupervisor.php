<?php

namespace App\Support;

use Symfony\Component\Process\Process;

class ConferenceSignalingServerSupervisor
{
    private static ?Process $process = null;

    public static function start(): ?Process
    {
        if (! config('conference_signaling.enabled', true)) {
            return null;
        }

        if (self::$process instanceof Process && self::$process->isRunning()) {
            return self::$process;
        }

        $host = trim((string) config('conference_signaling.server.bind_host', '127.0.0.1'));
        $port = (int) config('conference_signaling.server.port', 6001);

        if ($host === '' || $port < 1 || $port > 65535) {
            logger()->warning('Skipping signaling server startup due to invalid host/port.', [
                'host' => $host,
                'port' => $port,
            ]);

            return null;
        }

        $process = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'conference:signal-serve',
            '--host='.$host,
            '--port='.$port,
            '--parent-pid='.getmypid(),
        ], base_path(), null, null, null);

        $process->disableOutput();
        $process->start();

        self::$process = $process;

        register_shutdown_function(static function (): void {
            ConferenceSignalingServerSupervisor::stop();
        });

        logger()->info('Spawned conference signaling server process.', [
            'host' => $host,
            'port' => $port,
            'pid' => $process->getPid(),
        ]);

        return $process;
    }

    public static function stop(): void
    {
        if (! (self::$process instanceof Process)) {
            return;
        }

        if (self::$process->isRunning()) {
            self::$process->stop(3);
        }

        self::$process = null;
    }
}
