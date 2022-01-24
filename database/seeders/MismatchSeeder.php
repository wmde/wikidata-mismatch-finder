<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;

/**
 * Seeder for the Mismatch resource
 *
 * This seeder generates 42 Mismatch entries:
 * 10 unreviewed, unexpired,
 * 11 unreviewed, expired,
 * 10 reviewed, unexpired and
 * 11 reviewed, expired ones.
 */
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

        $expiredImport = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->expired()
            ->create([
                'status' => 'completed'
            ]);

        Mismatch::factory(10)
            ->for($import)
            ->create();

        Mismatch::factory(11)
            ->for($expiredImport)
            ->create();

        Mismatch::factory(10)
            ->for($import)
            ->reviewed()
            ->create();

        Mismatch::factory(11)
            ->for($expiredImport)
            ->reviewed()
            ->create();
    }
}
