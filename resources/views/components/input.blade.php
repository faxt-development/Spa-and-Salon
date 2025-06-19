@props(['name', 'type' => 'text', 'value' => '', 'required' => false])

@php
    $isTimeInput = $type === 'time';
    $baseClasses = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full';
    $timeInputClasses = 'appearance-none';
    $classes = $isTimeInput 
        ? $timeInputClasses . ' ' . $attributes->get('class', '')
        : $baseClasses;
@endif

<div class="relative">
    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge(['class' => $classes]) }}
        value="{{ old($name, $value) }}"
        @if($required) required @endif
    >
    @if($isTimeInput)
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
        </svg>
    </div>
    @endif
</div>

@error($name)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
