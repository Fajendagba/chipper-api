<?php

namespace Tests\Unit\Console\Commands;

use App\Jobs\ImportUsersJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportUsersCommandTest extends TestCase
{
    public function test_can_run_import_users_command(): void
    {
        Bus::fake();

        $this->artisan('users:import')
            ->assertExitCode(0);

        Bus::assertDispatched(ImportUsersJob::class);
    }
}
