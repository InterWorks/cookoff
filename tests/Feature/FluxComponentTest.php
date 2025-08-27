<?php

use App\Livewire\FluxTest;
use Livewire\Livewire;

describe('Flux Component Testing', function () {
    it('can render flux test page', function () {
        $response = $this->get('/flux-test');

        $response->assertStatus(200);
        $response->assertSee('Flux Component Test');
        $response->assertSee('Radio Buttons Test');
        $response->assertSee('Button Test');
        $response->assertSee('Input Test');
    });

    it('can test flux radio component interactivity', function () {
        $component = Livewire::test(FluxTest::class);

        // Initial state
        expect($component->get('radioSelection'))->toBe('');
        $component->assertSee('Current selection: None');

        // Try to set radio value
        $component->set('radioSelection', 'option2');
        expect($component->get('radioSelection'))->toBe('option2');
        $component->assertSee('Current selection: option2');

        // Check HTML output for debugging
        $html = $component->html();
        dump('=== FLUX TEST RADIO HTML ===');
        dump('Radio selection: '.$component->get('radioSelection'));
        dump('Has flux:radio components: '.(str_contains($html, 'flux:radio') ? 'YES' : 'NO'));
        dump('Has input elements: '.(str_contains($html, '<input') ? 'YES' : 'NO'));
        dump('Has wire:model: '.(str_contains($html, 'wire:model') ? 'YES' : 'NO'));
    });

    it('can test other flux components', function () {
        $component = Livewire::test(FluxTest::class);

        // Test button click
        $component->call('buttonClick');
        $component->assertSee('Button clicked successfully!');

        // Test input
        $component->set('inputValue', 'test input');
        expect($component->get('inputValue'))->toBe('test input');
        $component->assertSee('test input');

        // Test checkbox
        $component->set('checkboxValue', true);
        expect($component->get('checkboxValue'))->toBe(true);
        $component->assertSee('Current state: Checked');

        // Test select
        $component->set('selectValue', 'apple');
        expect($component->get('selectValue'))->toBe('apple');
        $component->assertSee('Current selection: "apple"');
    });
});
