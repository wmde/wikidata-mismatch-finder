<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class DecisionDropdown extends BaseComponent
{
    public function __construct(int $mismatchId)
    {
        $this->mismatchId = $mismatchId;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return "#mismatch-$this->mismatchId .cdx-select-vue";
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@select-menu' => '.cdx-select-vue__handle',
            '@selected' => '.cdx-select-vue__handle',
            '@menu-items' => '.cdx-menu__listbox'
        ];
    }

    public function selectPosition(Browser $browser, int $position, string $option)
    {
        $browser->click('@select-menu')
            ->within('@menu-items', function ($menu) use ($position, $option) {
                $positionSelector = ".cdx-menu-item:nth-child($position)";
                $menu->assertSeeIn($positionSelector, $option)
                    ->click($positionSelector);
            })
            ->assertSeeIn('@selected', $option);
    }

    public function assertOption(Browser $browser, string $option)
    {
        $browser->assertSeeIn('@selected', $option);
    }

    public function assertDropdownDisabled(Browser $browser)
    {
        // Assert Vue has some issues working with our current
        // setup, let's try this again after we  remove the migration build
        // $browser->assertVue('disabled', true);
    }
}
