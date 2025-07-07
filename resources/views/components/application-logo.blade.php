<a href="{{ route('home') }}" class="flex items-center">
    <img src="{{ asset('images/faxtina-logo.jpg') }}" alt="{{ $companyName }}" {{ $attributes->merge(['class' => 'h-12 w-auto']) }}>
    <span class="ml-3 text-xl font-display font-bold text-primary-700 dark:text-primary-400">{{ $companyName }}</span>
</a>
