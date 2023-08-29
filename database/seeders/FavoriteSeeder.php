<?php

namespace Database\Seeders;

use App\Models\Favorite;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public const FavoriteSeedCount = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Favorite::factory()
            ->count(self::FavoriteSeedCount)
            ->create();

        Favorite::factory()
            ->count(self::FavoriteSeedCount)
            ->forUser()
            ->create();
    }
}
