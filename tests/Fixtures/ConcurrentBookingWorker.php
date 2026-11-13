<?php

use App\Enums\SessionMode;
use App\Exceptions\BookingConflictException;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $clientId, $serviceId, $profileId, $startsAt, $barrier] = $argv;

while (microtime(true) < (float) $barrier) {
    usleep(1000);
}

try {
    app(BookingService::class)->createForClient(
        User::query()->findOrFail($clientId),
        Service::query()->findOrFail($serviceId),
        CounsellorProfile::query()->findOrFail($profileId),
        SessionMode::InPerson,
        CarbonImmutable::parse($startsAt)->utc(),
    );
    fwrite(STDOUT, 'CREATED');
} catch (BookingConflictException) {
    fwrite(STDOUT, 'CONFLICT');
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage());
    exit(2);
}
