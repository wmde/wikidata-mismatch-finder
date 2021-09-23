<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\HomePage;
use App\Models\Mismatch;
use App\Models\ImportMeta;
use App\Models\User;

class ItemsFormTest extends DuskTestCase
{

    use DatabaseMigrations;

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_can_enter_list_of_item_ids()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q1', '{return_key}', 'Q2')
                    ->assertInputValue('@items-input', "Q1\nQ2");
        });
    }

    public function test_can_submit_list_of_item_ids_not_present_in_db()
    {

        $this->browse(function (Browser $browser) {

            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q23', '{return_key}', 'Q42')
                    ->press('button')
                    ->waitFor('@not-found')
                    ->assertTitle('Mismatch Finder - Results')
                    ->assertSee('[ "Q23", "Q42" ]');
        });
    }

    public function test_can_submit_list_of_item_ids_present_in_db()
    {

        $this->browse(function (Browser $browser) {

            $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create();

            Mismatch::factory(2)
                ->for($import)
                ->create();
            
            $mismatch = Mismatch::first();
            $mismatch2 = Mismatch::find(2);

            $item1_in_db_id =  $mismatch->item_id;
            $item2_in_db_id =  $mismatch2->item_id;

            $browser->visit(new HomePage)
                    ->keys('@items-input', $item1_in_db_id, '{return_key}', $item2_in_db_id)
                    ->press('button')
                    ->waitFor('table')
                    ->assertTitle('Mismatch Finder - Results')
                    ->assertSee($item1_in_db_id)
                    ->assertSee($item2_in_db_id);
        });
    }

    public function test_empty_item_list_yields_warning()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->press('button')
                    ->assertSee('Please provide the Item identifiers in order to perform the check.');

            $this->assertStringContainsString('--warning', $browser->attribute('@items-input', 'class'));
        });
    }

    public function test_empty_list_warning_resolves()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->press('button')
                    ->assertSee('Please provide the Item identifiers in order to perform the check.')
                    ->keys('@items-input', 'Q1', '{return_key}', 'Q2')
                    ->press('button')
                    ->assertDontSee('Please provide the Item identifiers in order to perform the check.');
        });
    }

    public function test_invalid_item_list_yields_error()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q1234-invalid')
                    ->press('button')
                    ->assertSee('One or more Item identifiers couldn\'t be processed.');

            $this->assertStringContainsString('--error', $browser->attribute('@items-input', 'class'));
        });
    }
}
