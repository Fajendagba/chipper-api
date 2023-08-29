<?php

namespace App\Console\Commands;

use App\Jobs\ImportUsersJob;
use Illuminate\Console\Command;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import {limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users into Chipper from a JSON endpoint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ImportUsersJob::dispatch($this->argument('limit'));
    }
}
