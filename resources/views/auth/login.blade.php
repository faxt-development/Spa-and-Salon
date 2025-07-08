<x-guest-layout>
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
        <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 p-4 bg-green-50 text-green-700 rounded-md" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-md">
            <div class="font-medium">Whoops! Something went wrong.</div>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="block text-sm font-medium text-gray-700" />
            <div class="mt-1">
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="email"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                    placeholder="you@example.com"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="block text-sm font-medium text-gray-700" />
                @if (Route::has('password.request'))
                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-primary-600 hover:text-primary-500">
                            {{ __('Forgot your password?') }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="mt-1">
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                    placeholder="••••••••"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input
                id="remember_me"
                name="remember"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            >
            <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                {{ __('Remember me') }}
            </label>
        </div>

        <div>
            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
            >
                {{ __('Sign in') }}
            </button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">
                Don't have an account?
                <a href="{{ route('pricing') }}" class="font-medium text-primary-600 hover:text-primary-500">
                    {{ __('Sign up') }}
                </a>
            </p>
        </div>
    @endif
</x-guest-layout>
