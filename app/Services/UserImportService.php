<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserImportService
{
    /**
     * Fetch the users to be imported from the remote JSON endpoint
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function fetchUsers(): mixed
    {
        $response = Http::get(strval(config('services.json-user-import.url')));

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Error fetching users to import from json endpoint');
            return null;
        }
    }

    /**
     * Import the users who have been fetched into the database
     *
     * @param array $users
     * @param int $limit
     *
     * @return void
     */
    public function importUsers(array $users, int $limit = User::JSON_IMPORT_LIMIT): void
    {
        for($i = 0; $i < $limit; $i++) {
            User::create([
                'name'  => $users[$i]['name'],
                'email' => $users[$i]['email'],
                'password' => Hash::make(Str::random(8))
            ]);
        }
    }
}
