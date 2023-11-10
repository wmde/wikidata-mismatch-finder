<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;
use Tests\Browser\Components\DecisionDropdown;
use App\Models\Mismatch;

class ResultsPage extends Page
{
    /**
     * @var string
     */
    private $ids;

    public function __construct(?string $ids = null)
    {
        $this->ids = $ids;
    }
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return route('results', [
            'ids' => $this->ids
        ]);
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser
            ->assertPathIs('/results')
            ->assertPresent('header');
    }

    public function elements()
    {
        return [
            '@back-button' => '.back-button',
            '@confirmation-dialog' => '#results-confirmation-dialog',
            '@disable-confirmation' => '.disable-confirmation',
            '@error-section' => '#error-section',
            '@disable-confirmation-label' => '.disable-confirmation>.wikit-checkbox__label'
        ];
    }

    public function decideAndApply(Browser $browser, Mismatch $mismatch, array $decision)
    {
        $dropdownComponent = new DecisionDropdown($mismatch->id);

        $browser->within($dropdownComponent, function ($dropdown) use ($decision) {
           // select and assert option
            $dropdown->selectPosition($decision['option'], $decision['label']);
        })
        // ensure the correct apply button is pressed
        ->within("#item-mismatches-$mismatch->item_id", function ($section) {
            $section->press('Save reviews');
        });
    }
}
