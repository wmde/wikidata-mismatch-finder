<?php

namespace Tests\Unit;

use App\Rules\StatementGuidValue;
use PHPUnit\Framework\TestCase;

class StatementGuidValueTest extends TestCase
{
    public function test_passes_validation_when_ids_identical(): void
    {
        $rule = new StatementGuidValue();
        $rule->setData(['item_id' => 'Q184746']);
        $this->assertTrue($rule->passes(
            'statement_guid',
            'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5'
        ));
    }

    public function test_passes_validation_when_statement_guid_lowercased(): void
    {
        $rule = new StatementGuidValue();
        $rule->setData(['item_id' => 'Q111']);
        $this->assertTrue($rule->passes(
            'statement_guid',
            'q111$fc6ebc3f-4ea3-3cc8-0f09-a5608474754c'
        ));
    }

    public function test_fails_validation_when_ids_differ(): void
    {
        $rule = new StatementGuidValue();
        $rule->setData(['item_id' => 'Q111']);
        $this->assertFalse($rule->passes(
            'statement_guid',
            'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5'
        ));
    }
}
