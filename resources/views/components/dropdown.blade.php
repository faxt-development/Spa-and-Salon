@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '64' => 'w-64',
    '96' => 'w-96',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.away="open = false">
    <div @click="open = !open" @keydown.enter.prevent="open = !open" @keydown.space.prevent="open = !open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
         x-cloak
         x-ref="dropdown"
         role="menu"
         aria-orientation="vertical"
         aria-labelledby="menu-button"
         tabindex="-1"
         x-bind:aria-hidden="!open"
         style="display: none;"
         @click.away="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}" role="none">
            {{ $content }}
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
