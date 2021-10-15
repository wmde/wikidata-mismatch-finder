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
        return "#mismatch-$this->mismatchId .wikit-Dropdown";
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
            '@select-menu' => '.wikit-Dropdown__select',
            '@selected' => '.wikit-Dropdown__selectedOption',
            '@menu-items' => '.wikit-Dropdown__menu'
        ];
    }

    public function selectPosition(Browser $browser, int $position, string $option)
    {
        $browser->click('@select-menu')
            ->within('@menu-items', function ($menu) use ($position, $option) {
                $positionSelector = ".wikit-OptionsMenu__item:nth-child($position)";
                $menu->assertSeeIn($positionSelector, $option)
                    ->click($positionSelector);
            })
            ->assertSeeIn('@selected', $option);
    }

    public function assertOption(Browser $browser, string $option)
    {
        $browser->assertSeeIn('@selected', $option);
    }
}
