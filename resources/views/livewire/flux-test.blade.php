<div class="space-y-8">
    <flux:heading size="xl">Flux Component Test Page</flux:heading>
    <flux:subheading>Testing various Flux components to isolate any issues</flux:subheading>

    {{-- Success/Flash Messages --}}
    @if (session()->has('success'))
        <flux:callout icon="check-circle" variant="success">
            <flux:callout.heading>Success!</flux:callout.heading>
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Radio Buttons Test --}}
    <flux:card>
        <flux:heading size="lg">Radio Buttons Test</flux:heading>
        <flux:subheading>Current selection: {{ $radioSelection ?: 'None' }}</flux:subheading>
        
        <flux:radio.group wire:model.live="radioSelection" label="Choose an option" class="mt-4">
            <flux:radio value="option1" label="Option 1" />
            <flux:radio value="option2" label="Option 2" />
            <flux:radio value="option3" label="Option 3" />
        </flux:radio.group>

        <div class="mt-4 text-sm text-gray-600">
            <strong>Debug:</strong> Radio value = "{{ $radioSelection }}"
        </div>
    </flux:card>

    {{-- Button Test --}}
    <flux:card>
        <flux:heading size="lg">Button Test</flux:heading>
        <flux:button wire:click="buttonClick" variant="primary">
            Click Me (Livewire Action)
        </flux:button>
    </flux:card>

    {{-- Input Test --}}
    <flux:card>
        <flux:heading size="lg">Input Test</flux:heading>
        <flux:subheading>Current value: "{{ $inputValue }}"</flux:subheading>
        
        <flux:input 
            wire:model.live="inputValue" 
            label="Test Input" 
            placeholder="Type something..."
        />
        
        <div class="mt-4 text-sm text-gray-600">
            <strong>Debug:</strong> Input value = "{{ $inputValue }}"
        </div>
    </flux:card>

    {{-- Select Test --}}
    <flux:card>
        <flux:heading size="lg">Select Test</flux:heading>
        <flux:subheading>Current selection: "{{ $selectValue }}"</flux:subheading>
        
        <flux:select wire:model.live="selectValue" label="Choose from dropdown">
            <option value="">-- Select an option --</option>
            <option value="apple">Apple</option>
            <option value="banana">Banana</option>
            <option value="cherry">Cherry</option>
        </flux:select>
        
        <div class="mt-4 text-sm text-gray-600">
            <strong>Debug:</strong> Select value = "{{ $selectValue }}"
        </div>
    </flux:card>

    {{-- Checkbox Test --}}
    <flux:card>
        <flux:heading size="lg">Checkbox Test</flux:heading>
        <flux:subheading>Current state: {{ $checkboxValue ? 'Checked' : 'Unchecked' }}</flux:subheading>
        
        <flux:checkbox 
            wire:model.live="checkboxValue" 
            label="Test checkbox"
        />
        
        <div class="mt-4 text-sm text-gray-600">
            <strong>Debug:</strong> Checkbox value = {{ $checkboxValue ? 'true' : 'false' }}
        </div>
    </flux:card>

    {{-- Switch Test --}}
    <flux:card>
        <flux:heading size="lg">Switch Test</flux:heading>
        <flux:subheading>Current state: {{ $switchValue ? 'On' : 'Off' }}</flux:subheading>
        
        <flux:switch 
            wire:model.live="switchValue" 
            label="Test switch"
        />
        
        <div class="mt-4 text-sm text-gray-600">
            <strong>Debug:</strong> Switch value = {{ $switchValue ? 'true' : 'false' }}
        </div>
    </flux:card>

    {{-- Static Components Test --}}
    <flux:card>
        <flux:heading size="lg">Static Components</flux:heading>
        <flux:subheading>These should render without interactivity</flux:subheading>
        
        <div class="space-y-4">
            <flux:badge>Badge Test</flux:badge>
            
            <flux:callout icon="information-circle" variant="info">
                <flux:callout.heading>Info Callout</flux:callout.heading>
                <flux:callout.text>This is an informational callout.</flux:callout.text>
            </flux:callout>

            <flux:separator />

            <flux:text variant="muted">Muted text component</flux:text>
        </div>
    </flux:card>
</div>
