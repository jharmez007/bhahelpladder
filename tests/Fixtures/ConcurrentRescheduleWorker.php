<?php

use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $actorId, $appointmentId, $startsAt, $barrier] = $argv;

while (microtime(true) < (float) $barrier) {
    usleep(1000);
}

try {
    app(BookingService::class)->reschedule(
        User::query()->findOrFail($actorId),
        Appointment::query()->findOrFail($appointmentId),
        CarbonImmutable::parse($startsAt)->utc(),
    );
    fwrite(STDOUT, 'MOVED');
} catch (BookingConflictException) {
    fwrite(STDOUT, 'CONFLICT');
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage());
    exit(2);
}
