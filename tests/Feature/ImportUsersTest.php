<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportUsersTest extends TestCase
{
    use DatabaseMigrations;

    protected function getFakeUsersResponse(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Leanne Graham',
                'username' => 'Bret',
                'email' => 'Sincere@april.biz',
                'address' => ['street' => 'Kulas Light', 'city' => 'Gwenborough'],
                'phone' => '1-770-736-8031',
                'website' => 'hildegard.org',
                'company' => ['name' => 'Romaguera-Crona'],
            ],
            [
                'id' => 2,
                'name' => 'Ervin Howell',
                'username' => 'Antonette',
                'email' => 'Shanna@melissa.tv',
                'address' => ['street' => 'Victor Plains', 'city' => 'Wisokyburgh'],
                'phone' => '010-692-6593',
                'website' => 'anastasia.net',
                'company' => ['name' => 'Deckow-Crist'],
            ],
            [
                'id' => 3,
                'name' => 'Clementine Bauch',
                'username' => 'Samantha',
                'email' => 'Nathan@yesenia.net',
                'address' => ['street' => 'Douglas Extension', 'city' => 'McKenziehaven'],
                'phone' => '1-463-123-4447',
                'website' => 'ramiro.info',
                'company' => ['name' => 'Romaguera-Jacobson'],
            ],
            [
                'id' => 4,
                'name' => 'Patricia Lebsack',
                'username' => 'Karianne',
                'email' => 'Julianne.OConner@kory.org',
                'address' => ['street' => 'Hoeger Mall', 'city' => 'South Elvis'],
                'phone' => '493-170-9623',
                'website' => 'kale.biz',
                'company' => ['name' => 'Robel-Corkery'],
            ],
            [
                'id' => 5,
                'name' => 'Chelsey Dietrich',
                'username' => 'Kamren',
                'email' => 'Lucio_Hettinger@annie.ca',
                'address' => ['street' => 'Skiles Walks', 'city' => 'Roscoeview'],
                'phone' => '(254)954-1289',
                'website' => 'demarco.info',
                'company' => ['name' => 'Keebler LLC'],
            ],
        ];
    }

    public function test_command_imports_users_from_url()
    {
        Http::fake([
            'https://example.com/users' => Http::response($this->getFakeUsersResponse(), 200),
        ]);

        $this->artisan('users:import', ['url' => 'https://example.com/users', 'limit' => 5])
            ->expectsOutput('Importing 5 users...')
            ->expectsOutput('Successfully imported 5 users.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseHas('users', [
            'name' => 'Leanne Graham',
            'email' => 'sincere@april.biz',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Ervin Howell',
            'email' => 'shanna@melissa.tv',
        ]);
    }

    public function test_command_respects_limit()
    {
        Http::fake([
            'https://example.com/users' => Http::response($this->getFakeUsersResponse(), 200),
        ]);

        $this->artisan('users:import', ['url' => 'https://example.com/users', 'limit' => 3])
            ->expectsOutput('Importing 3 users...')
            ->expectsOutput('Successfully imported 3 users.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 3);
    }

    public function test_command_handles_invalid_url()
    {
        Http::fake([
            'https://invalid.com/users' => Http::response(null, 500),
        ]);

        $this->artisan('users:import', ['url' => 'https://invalid.com/users', 'limit' => 5])
            ->expectsOutput('Failed to fetch data from URL.')
            ->assertFailed();
    }

    public function test_command_updates_existing_users()
    {
        User::factory()->create([
            'name' => 'Old Name',
            'email' => 'sincere@april.biz',
        ]);

        Http::fake([
            'https://example.com/users' => Http::response([
                [
                    'id' => 1,
                    'name' => 'Leanne Graham',
                    'username' => 'Bret',
                    'email' => 'Sincere@april.biz',
                ],
            ], 200),
        ]);

        $this->artisan('users:import', ['url' => 'https://example.com/users', 'limit' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Leanne Graham',
            'email' => 'sincere@april.biz',
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'Old Name',
        ]);
    }
}
