<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

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
}
