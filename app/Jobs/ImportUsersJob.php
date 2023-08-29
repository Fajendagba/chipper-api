<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\UserImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $limit = User::JSON_IMPORT_LIMIT)
    {
        //
    }

    /**
     * Execute the job to import users from a remote JSON source.
     *
     * @param \App\Services\UserImportService
     *
     * @return void
     */
    public function handle(UserImportService $userImportService): void
    {
        $users = $userImportService->fetchUsers();

        DB::transaction(function () use ($userImportService, $users) {
            $userImportService->importUsers($users, $this->limit);
        });
    }
}
