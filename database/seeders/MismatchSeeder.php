<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;

class MismatchSeeder extends Seeder
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
            ->create();

        Mismatch::factory(21)
            ->for($import)
            ->create();

        Mismatch::factory(21)
            ->for($import)
            ->edited()
            ->create();
    }
}
