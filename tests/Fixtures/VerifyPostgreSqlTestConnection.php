<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$connection = (string) config('database.default');
$configuredDatabase = (string) config('database.connections.pgsql.database');

if ($connection !== 'pgsql' || $configuredDatabase !== 'bha_help_ladder_test') {
    fwrite(STDERR, "Refusing to continue: the configured database is not bha_help_ladder_test.\n");
    exit(1);
}

$actualDatabase = (string) DB::selectOne('select current_database() as name')->name;

if ($actualDatabase !== 'bha_help_ladder_test') {
    fwrite(STDERR, "Refusing to continue: PostgreSQL connected to an unexpected database.\n");
    exit(1);
}

fwrite(STDOUT, "Verified isolated PostgreSQL test database: {$actualDatabase}.\n");
