@props(['for' => null, 'value' => null])

<label {{ $for ? 'for='.$for : '' }} {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>