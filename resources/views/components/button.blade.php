<button {{ $attributes->merge(['class' => 'inline-flex items-center px-4 py-2 bg-primary text-white font-semibold rounded-md shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 transition-colors']) }}>
    {{ $slot }}
</button>
