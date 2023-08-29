<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ImportUsersJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportUsersJobTest extends TestCase
{
    public function test_can_dispatch_import_users_job(): void
    {
        Bus::fake();

        ImportUsersJob::dispatch();

        Bus::assertDispatched(ImportUsersJob::class);
    }
}
