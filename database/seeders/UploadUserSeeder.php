<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UploadUser;

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
