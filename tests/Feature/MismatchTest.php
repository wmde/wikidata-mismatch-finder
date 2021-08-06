<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MismatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_ItemIdFromStatementGuid()
    {
        $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create();

        Mismatch::factory()
            ->for($import)
            ->create();
        
        $mismatch = Mismatch::first();

        $this->assertEquals(
            explode('$', $mismatch->statement_guid, 2)[0],
            $mismatch->item_id
        );
    }
}
