<section x-data="{
    form: {
        name: '{{ old('name', $user->name) }}',
        email: '{{ old('email', $user->email) }}',
        birthday: '{{ old('birthday', $user->birthday ? $user->birthday->format('Y-m-d') : '') }}',
        phone_number: '{{ old('phone_number', $user->phone_number) }}',
        address: '{{ old('address', $user->address) }}',
        city: '{{ old('city', $user->city) }}',
        state: '{{ old('state', $user->state) }}',
        zip_code: '{{ old('zip_code', $user->zip_code) }}',
        sms_notifications: {{ old('sms_notifications', $user->sms_notifications) ? 'true' : 'false' }},
        email_notifications: {{ old('email_notifications', $user->email_notifications) ? 'true' : 'false' }},
        appointment_reminders: {{ old('appointment_reminders', $user->appointment_reminders) ? 'true' : 'false' }},
        promotional_emails: {{ old('promotional_emails', $user->promotional_emails) ? 'true' : 'false' }},
        receive_newsletter: {{ old('receive_newsletter', $user->receive_newsletter) ? 'true' : 'false' }},
        receive_special_offers: {{ old('receive_special_offers', $user->receive_special_offers) ? 'true' : 'false' }},
        receive_product_updates: {{ old('receive_product_updates', $user->receive_product_updates) ? 'true' : 'false' }},
    },
    submitForm() {
        $refs.updateProfileForm.submit();
    }
}">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your account\'s profile information and email address.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" x-ref="updateProfileForm">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" x-model="form.name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" x-model="form.email" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <h3 class="text-lg font-medium text-gray-900 mt-8">{{ __('Personal Information') }}</h3>

        <!-- Birthday -->
        <div>
            <x-input-label for="birthday" :value="__('Birthday')" />
            <x-text-input id="birthday" name="birthday" type="date" class="mt-1 block w-full" x-model="form.birthday" />
            <x-input-error class="mt-2" :messages="$errors->get('birthday')" />
        </div>

        <!-- Phone Number -->
        <div>
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" x-model="form.phone_number" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>

        <!-- Address -->
        <div>
            <x-input-label for="address" :value="__('Address')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" x-model="form.address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <!-- City -->
        <div>
            <x-input-label for="city" :value="__('City')" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" x-model="form.city" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <!-- State -->
        <div>
            <x-input-label for="state" :value="__('State')" />
            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" x-model="form.state" />
            <x-input-error class="mt-2" :messages="$errors->get('state')" />
        </div>

        <!-- Zip Code -->
        <div>
            <x-input-label for="zip_code" :value="__('Zip Code')" />
            <x-text-input id="zip_code" name="zip_code" type="text" class="mt-1 block w-full" x-model="form.zip_code" />
            <x-input-error class="mt-2" :messages="$errors->get('zip_code')" />
        </div>

        <h3 class="text-lg font-medium text-gray-900 mt-8">{{ __('Notification Preferences') }}</h3>

        <!-- SMS Notifications -->
        <div class="flex items-center">
            <input id="sms_notifications" name="sms_notifications" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.sms_notifications" value="1">
            <x-input-label for="sms_notifications" :value="__('Receive SMS Notifications')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('sms_notifications')" />
        </div>

        <!-- Email Notifications -->
        <div class="flex items-center">
            <input id="email_notifications" name="email_notifications" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.email_notifications" value="1">
            <x-input-label for="email_notifications" :value="__('Receive Email Notifications')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('email_notifications')" />
        </div>

        <!-- Appointment Reminders -->
        <div class="flex items-center">
            <input id="appointment_reminders" name="appointment_reminders" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.appointment_reminders" value="1">
            <x-input-label for="appointment_reminders" :value="__('Receive Appointment Reminders')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('appointment_reminders')" />
        </div>

        <h3 class="text-lg font-medium text-gray-900 mt-8">{{ __('Email Preferences') }}</h3>

        <!-- Promotional Emails -->
        <div class="flex items-center">
            <input id="promotional_emails" name="promotional_emails" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.promotional_emails" value="1">
            <x-input-label for="promotional_emails" :value="__('Receive Promotional Emails')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('promotional_emails')" />
        </div>

        <!-- Newsletter -->
        <div class="flex items-center">
            <input id="receive_newsletter" name="receive_newsletter" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.receive_newsletter" value="1">
            <x-input-label for="receive_newsletter" :value="__('Receive Newsletter')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('receive_newsletter')" />
        </div>

        <!-- Special Offers -->
        <div class="flex items-center">
            <input id="receive_special_offers" name="receive_special_offers" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.receive_special_offers" value="1">
            <x-input-label for="receive_special_offers" :value="__('Receive Special Offers')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('receive_special_offers')" />
        </div>

        <!-- Product Updates -->
        <div class="flex items-center">
            <input id="receive_product_updates" name="receive_product_updates" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="form.receive_product_updates" value="1">
            <x-input-label for="receive_product_updates" :value="__('Receive Product Updates')" class="ml-2" />
            <x-input-error class="mt-2" :messages="$errors->get('receive_product_updates')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button @click="submitForm">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
