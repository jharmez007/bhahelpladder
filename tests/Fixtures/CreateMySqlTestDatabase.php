<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

DB::statement('CREATE DATABASE IF NOT EXISTS `bha_help_ladder_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

fwrite(STDOUT, "MySQL test database is ready.\n");
