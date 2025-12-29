<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class ImportUsers extends Command
{
    protected $signature = 'users:import {url} {limit}';

    protected $description = 'Import users from a JSON URL';

    public function handle(): int
    {
        $url = $this->argument('url');
        $limit = (int) $this->argument('limit');

        try {
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error('Failed to fetch data from URL.');
                return Command::FAILURE;
            }

            $users = $response->json();

            if (!is_array($users)) {
                $this->error('Invalid JSON response.');
                return Command::FAILURE;
            }

            $usersToImport = array_slice($users, 0, $limit);
            $count = count($usersToImport);

            $this->info("Importing {$count} users...");

            foreach ($usersToImport as $userData) {
                User::updateOrCreate(
                    ['email' => strtolower($userData['email'])],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make('password'),
                    ]
                );
            }

            $this->info("Successfully imported {$count} users.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error importing users: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
