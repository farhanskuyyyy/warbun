<x-auth-layout :title="__('Confirm Password')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p><h1>{{ __('Confirm Password') }}</h1><p class="auth-form-description">{{ __('Enter your password to continue to your account settings.') }}</p>
    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">@csrf
        <x-password-field />
        <button type="submit" class="button-primary auth-submit">{{ __('Confirm') }}</button>
    </form>
</x-auth-layout>
