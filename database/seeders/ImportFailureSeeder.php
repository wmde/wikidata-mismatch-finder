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
        $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create([
                'status' => 'failed'
            ]);

        ImportFailure::factory(3)
            ->for($import)
            ->create();
    }
}
