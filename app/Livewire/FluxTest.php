<?php

namespace App\Livewire;

use Livewire\Component;

class FluxTest extends Component
{
    public $radioSelection = '';

    public $checkboxValue = false;

    public $selectValue = '';

    public $inputValue = '';

    public $switchValue = false;

    /**
     * Handle button click event.
     *
     * @return void
     */
    public function buttonClick()
    {
        session()->flash('success', 'Button clicked successfully!');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.flux-test')
            ->layout('components.layout', [
                'heading'    => 'Flux Component Test',
                'subheading' => 'Testing Flux UI components for functionality',
            ]);
    }
}
