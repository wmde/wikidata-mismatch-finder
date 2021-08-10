<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImportMeta;
use App\Models\User;
use App\Models\ImportFailure;

class ImportFailureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->uploader()->create();

        // Create 3 failed imports
        for ($i = 0; $i < 3; $i++) {
            $import = ImportMeta::factory()
                ->failed()
                ->for($user);

            ImportFailure::factory()
                ->for($import)
                ->create();
        }

    }
}
