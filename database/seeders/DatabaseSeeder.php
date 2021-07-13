<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UploadUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed 5 users
        $this->seedUsers(5);

        // Seeds 1 uploader only once
        $this->seedUploader();
    }

    private function seedUsers(int $amount): void
    {
        if ($amount < 1) {
            return;
        }

        // Create random Users
        User::factory($amount)->create();
    }

    private function seedUploader(): void
    {
        $test_username = 'test_user';

        // Create 1 static user for testing with upload permissions
        User::firstOrCreate([
            'username' => $test_username,
            'mw_userid' => 12345678
        ]);

        UploadUser::firstOrCreate([
            'username' => $test_username
        ]);
    }
}
