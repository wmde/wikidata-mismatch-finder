<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Generic Database Seeder
 *
 * This seeder calls all other seeders for the User, UploadUser,
 * ImportMeta, ImportFailure and Mismatch respources.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            UploadUserSeeder::class,
            ImportMetaSeeder::class,
            MismatchSeeder::class,
            ImportFailureSeeder::class
        ]);
    }
}
