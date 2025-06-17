@props(['name', 'type' => 'text', 'value' => '', 'required' => false])

<input 
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $name }}"
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full'])->merge($attributes->whereStartsWith('class')) }}
    value="{{ old($name, $value) }}"
    @if($required) required @endif
>

@error($name)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
