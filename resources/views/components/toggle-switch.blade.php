@props([
    'id' => null,
    'name' => null,
    'checked' => true,
    'label' => null,
    'labelPosition' => 'right',
    'onChange' => null,
])

@php
    $id = $id ?? 'toggle-' . Str::random(8);
    $switchId = $id . '-switch';
    $handleId = $id . '-handle';
    $statusId = $id . '-status';
@endphp

<div class="flex items-center gap-2 {{ $labelPosition === 'right' ? '' : 'flex-row-reverse' }}">
    @if($label && $labelPosition === 'left')
        <label for="{{ $id }}" class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    
    <button type="button" 
        id="{{ $switchId }}"
        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 {{ $checked ? 'bg-green-500' : 'bg-gray-200' }}"
        role="switch"
        aria-checked="{{ $checked ? 'true' : 'false' }}"
        data-state="{{ $checked ? 'checked' : 'unchecked' }}"
        onclick="toggleSwitchComponent('{{ $id }}', {{ $onChange ? 'true' : 'false' }})">
        <span 
            id="{{ $handleId }}"
            class="pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform {{ $checked ? 'translate-x-5' : 'translate-x-0' }}"
            data-state="{{ $checked ? 'checked' : 'unchecked' }}"></span>
        <input type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="1"
            class="sr-only"
            {{ $checked ? 'checked' : '' }}>
    </button>
    
    @if($label && $labelPosition === 'right')
        <label for="{{ $id }}" class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    
    <span id="{{ $statusId }}" class="text-sm text-gray-500 {{ isset($attributes['show-status']) && $attributes['show-status'] ? '' : 'hidden' }}">
        {{ $checked ? 'On' : 'Off' }}
    </span>
</div>

@once
    @push('scripts')
    <script>
        window.toggleSwitchComponent = function(id, hasCustomOnChange) {
            const checkbox = document.getElementById(id);
            const switchBtn = document.getElementById(id + '-switch');
            const switchHandle = document.getElementById(id + '-handle');
            const statusText = document.getElementById(id + '-status');
            
            if (!checkbox || !switchBtn || !switchHandle) return;
            
            // Toggle checkbox state
            checkbox.checked = !checkbox.checked;
            const isChecked = checkbox.checked;
            
            // Update switch appearance
            if (isChecked) {
                switchBtn.classList.remove('bg-gray-200');
                switchBtn.classList.add('bg-green-500');
                switchBtn.setAttribute('aria-checked', 'true');
                switchBtn.setAttribute('data-state', 'checked');
                switchHandle.classList.remove('translate-x-0');
                switchHandle.classList.add('translate-x-5');
                switchHandle.setAttribute('data-state', 'checked');
                if (statusText) statusText.textContent = 'On';
            } else {
                switchBtn.classList.remove('bg-green-500');
                switchBtn.classList.add('bg-gray-200');
                switchBtn.setAttribute('aria-checked', 'false');
                switchBtn.setAttribute('data-state', 'unchecked');
                switchHandle.classList.remove('translate-x-5');
                switchHandle.classList.add('translate-x-0');
                switchHandle.setAttribute('data-state', 'unchecked');
                if (statusText) statusText.textContent = 'Off';
            }
            
            // Trigger change event on checkbox for event listeners
            if (!hasCustomOnChange) {
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            }
            
            // Return the new state in case the caller needs it
            return isChecked;
        };
    </script>
    @endpush
@endonce
