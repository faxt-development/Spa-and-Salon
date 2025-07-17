<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow']) }}>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
