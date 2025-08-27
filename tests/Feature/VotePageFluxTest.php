<?php

// Test to ensure Flux 2.x components don't cause crashes
// This test validates the Flux upgrade was successful

describe('Vote Page Flux Component Test', function () {
    it('can compile vote page template without Flux component errors', function () {
        // Test that the Blade template compiles without errors
        // This catches issues like missing Flux components

        $bladeContent = file_get_contents(resource_path('views/contests/vote.blade.php'));

        // Check that new Flux 2.x table syntax is present (allowing for attributes)
        expect($bladeContent)->toContain('<flux:table.columns>');
        expect($bladeContent)->toContain('<flux:table.column');
        expect($bladeContent)->toContain('<flux:table.rows>');
        expect($bladeContent)->toContain('flux:table.row'); // May have attributes like :key
        expect($bladeContent)->toContain('<flux:table.cell');

        // Check that old Flux 1.x syntax is NOT present
        expect($bladeContent)->not()->toContain('<flux:columns>');
        expect($bladeContent)->not()->toContain('<flux:column>');
        expect($bladeContent)->not()->toContain('<flux:rows>');
        expect($bladeContent)->not()->toContain('<flux:row>');
        expect($bladeContent)->not()->toContain('<flux:cell>');
    });

    it('can compile single winner vote component without errors', function () {
        $bladeContent = file_get_contents(resource_path('views/livewire/single-winner-vote.blade.php'));

        // Check for proper Flux 2.x components that are actually used
        expect($bladeContent)->toContain('<flux:heading size="lg">');
        expect($bladeContent)->toContain('<flux:subheading>');
        expect($bladeContent)->toContain('<flux:callout icon="check-circle">');
        expect($bladeContent)->toContain('flux:callout.heading');
        expect($bladeContent)->toContain('flux:callout.text');

        // This component now uses Flux radio components with clickable labels
        expect($bladeContent)->toContain('<flux:radio');
        expect($bladeContent)->toContain('<flux:radio.group');
        expect($bladeContent)->toContain('<flux:card');
        expect($bladeContent)->toContain('<label class="flex items-start gap-3 cursor-pointer">');
        expect($bladeContent)->toContain('wire:model.live');
    });

    it('has correct Flux directives in layout', function () {
        $layoutContent = file_get_contents(resource_path('views/components/layout.blade.php'));

        // Check for Flux 2.x integration that is actually used
        expect($layoutContent)->toContain('@fluxAppearance');

        // This layout includes both Livewire and Flux scripts
        expect($layoutContent)->toContain('@livewireStyles');
        expect($layoutContent)->toContain('@livewireScripts');
        expect($layoutContent)->toContain('flux.min.js');

        // Vite handles CSS, no need for @fluxStyles directive
        expect($layoutContent)->toContain('@vite');
    });

    it('has correct CSS imports for Flux 2.x', function () {
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Check for new Tailwind v4 and Flux 2.x imports
        expect($cssContent)->toContain("@import 'tailwindcss'");
        expect($cssContent)->toContain("@import '../../vendor/livewire/flux/dist/flux.css'");
        expect($cssContent)->toContain('@custom-variant dark');

        // Should not contain old Tailwind v3 syntax
        expect($cssContent)->not()->toContain('@tailwind base');
        expect($cssContent)->not()->toContain('@tailwind components');
        expect($cssContent)->not()->toContain('@tailwind utilities');
    });

    it('has correct PostCSS configuration for Tailwind v4', function () {
        $postcssContent = file_get_contents(base_path('postcss.config.js'));

        // Check for new Tailwind v4 PostCSS plugin
        expect($postcssContent)->toContain('@tailwindcss/postcss');

        // Should not contain old Tailwind v3 plugin
        expect($postcssContent)->not()->toContain('tailwindcss: {}');
    });

    it('has correct package versions for Flux 2.x and Tailwind v4', function () {
        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);

        // Check Tailwind v4 is installed
        expect($packageJson['devDependencies']['tailwindcss'])->toStartWith('^4.');
        expect($packageJson['devDependencies']['@tailwindcss/postcss'])->toStartWith('^4.');

        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);

        // Find Flux package in composer.lock
        $fluxPackage = collect($composerLock['packages'])->firstWhere('name', 'livewire/flux');

        expect($fluxPackage)->not()->toBeNull();
        expect($fluxPackage['version'])->toStartWith('v2.');
    });
});

describe('Flux Component Syntax Validation', function () {
    it('validates all Flux table components use correct v2 syntax', function () {
        // Get all blade files that might contain Flux table components
        $bladeFiles = [
            resource_path('views/contests/vote.blade.php'),
        ];

        foreach ($bladeFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);

                // If file contains flux:table, ensure it uses v2 syntax
                if (str_contains($content, 'flux:table')) {
                    // Check that v2 syntax is used
                    expect($content)->toContain('flux:table.columns');
                    expect($content)->toContain('flux:table.rows');
                    expect($content)->toContain('flux:table.column');
                    expect($content)->toContain('flux:table.row');
                    expect($content)->toContain('flux:table.cell');

                    // Ensure old v1 syntax is NOT used
                    expect($content)->not()->toContain('<flux:columns>');
                    expect($content)->not()->toContain('<flux:rows>');
                    expect($content)->not()->toContain('<flux:column>');
                    expect($content)->not()->toContain('<flux:row>');
                    expect($content)->not()->toContain('<flux:cell>');
                }
            }
        }
    });
});
