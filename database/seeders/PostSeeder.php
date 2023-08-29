<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public const PostSeedCount = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::factory()
            ->count(self::PostSeedCount)
            ->create();
    }
}
