<?php

namespace Tests\Unit;

use App\Models\Mismatch;
use PHPUnit\Framework\TestCase;

class MismatchTest extends TestCase
{
    public function test_ItemIdFromStatementGuid()
    {
        $mismatch = new Mismatch([
            'statement_guid' => 'Q1234$some-uuid'
            ]);

        $this->assertEquals('Q1234', $mismatch->item_id);
    }
}
