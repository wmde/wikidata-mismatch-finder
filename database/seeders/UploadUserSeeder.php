<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * Seeder for the UploadUser resource
 *
 * This seeder generates an UploadUser entry.
 */
class UploadUserSeeder extends Seeder
{
    public const TEST_USER = 'Test Uploader';

    /**
     * Creates an upload user ONCE
     *
     * @return void
     */
    public function run()
    {
        User::factory()
            ->uploader(self::TEST_USER)
            ->create();
    }
}
