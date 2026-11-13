<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class WaitForDatabase extends Command
{
    protected $signature = 'app:wait-for-database {--attempts=20} {--delay=3}';

    protected $description = 'Wait a bounded time for the configured database to accept connections';

    public function handle(): int
    {
        $attempts = filter_var($this->option('attempts'), FILTER_VALIDATE_INT);
        $delay = filter_var($this->option('delay'), FILTER_VALIDATE_INT);

        if ($attempts === false || $attempts < 1 || $attempts > 60
            || $delay === false || $delay < 0 || $delay > 30) {
            $this->error('Database wait options are invalid.');

            return self::INVALID;
        }

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                DB::connection()->getPdo();
                DB::select('select 1');
                $this->info('Database connection is ready.');

                return self::SUCCESS;
            } catch (Throwable) {
                $this->warn("Database is not ready (attempt {$attempt} of {$attempts}).");
            }

            if ($attempt < $attempts && $delay > 0) {
                sleep($delay);
            }
        }

        $this->error('Database did not become ready before the retry limit.');

        return self::FAILURE;
    }
}
