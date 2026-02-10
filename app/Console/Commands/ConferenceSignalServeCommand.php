<?php

namespace App\Console\Commands;

use App\Realtime\ConferenceSignalingServer;
use Illuminate\Console\Command;
use Workerman\Timer;
use Workerman\Worker;

class ConferenceSignalServeCommand extends Command
{
    protected $signature = 'conference:signal-serve
        {--host= : Bind host for WebSocket server}
        {--port= : Bind port for WebSocket server}
        {--parent-pid= : Parent process ID to monitor for graceful shutdown}';

    protected $description = 'Start the custom conference WebSocket signaling server';

    public function handle(ConferenceSignalingServer $signalingServer): int
    {
        $host = trim((string) ($this->option('host') ?: config('conference_signaling.server.bind_host', '0.0.0.0')));
        $port = (int) ($this->option('port') ?: config('conference_signaling.server.port', 6001));
        $parentPid = (int) ($this->option('parent-pid') ?: 0);

        if ($host === '' || $port < 1 || $port > 65535) {
            $this->error('Invalid signaling host or port.');
            return self::FAILURE;
        }

        // --- CRITICAL FIX START ---
        // Workerman expects command line arguments like "start".
        // Since we are inside Artisan, we must manually set this so Workerman knows what to do.
        global $argv;
        $argv = [
            'artisan', // The script name (doesn't matter much)
            'start'    // The command Workerman expects
        ];
        // --- CRITICAL FIX END ---

        $worker = $signalingServer->buildWorker($host, $port);
        $this->info("Conference signaling server listening on ws://{$host}:{$port}");

        $worker->onWorkerStart = function () use ($host, $port, $parentPid): void {
            logger()->info('Conference signaling server started', [
                'host' => $host,
                'port' => $port,
                'parent_pid' => $parentPid > 0 ? $parentPid : null,
            ]);
            
            // Note: In Docker/Supervisor, we usually don't need the parent PID check 
            // because Supervisor manages the process life cycle.
        };

        $worker->onWorkerStop = function () use ($host, $port): void {
            logger()->info('Conference signaling server stopped', [
                'host' => $host,
                'port' => $port,
            ]);
        };

        // This will now block and run forever, keeping the server alive
        Worker::runAll();

        return self::SUCCESS;
    }

    private static function isProcessAlive(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            return function_exists('posix_kill') && @posix_kill($pid, 0);
        }

        $output = [];
        @exec("tasklist /FI \"PID eq {$pid}\" /NH", $output);
        $firstLine = '';
        foreach ($output as $line) {
            $candidate = strtolower(trim((string) $line));
            if ($candidate !== '') {
                $firstLine = $candidate;
                break;
            }
        }

        if ($firstLine === '' || str_contains($firstLine, 'no tasks')) {
            return false;
        }

        return str_contains($firstLine, (string) $pid);
    }
}
