<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserImportService;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ImportUserServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_insert_imported_users_into_the_database(): void
    {
        // fake the users json response
        $file = resource_path() . '/users.json';

        if (File::exists($file)) {
            $users = json_decode(file_get_contents($file), true);

            $service = new UserImportService();
            $service->importUsers($users, User::JSON_IMPORT_LIMIT);

            $this->assertDatabaseHas('users', [
                'id' => $users[0]['id'],
                'name' => $users[0]['name'],
                'email' => $users[0]['email'],
            ]);
        }
    }
}
