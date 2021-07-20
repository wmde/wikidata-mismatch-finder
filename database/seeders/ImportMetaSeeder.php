<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImportMeta;
use App\Models\User;

class ImportMetaSeeder extends Seeder
{
    /**
     * Create imports
     *
     * @return void
     */
    public function run()
    {
        // 10 Imports belong to current uploaders
        ImportMeta::factory(10)
            ->for(User::factory()->uploader())
            ->create();

        // 3 Imports belong to non uploaders
        ImportMeta::factory(3)
            ->forUser()
            ->create();
    }
}
