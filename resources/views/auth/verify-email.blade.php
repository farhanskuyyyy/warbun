<x-auth-layout :title="__('Verify your email')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p><h1>{{ __('Verify your email') }}</h1><p class="auth-form-description">{{ __('Check your inbox and follow the verification link. You can request another email below.') }}</p>
    <form method="POST" action="{{ route('verification.send') }}" class="auth-form">@csrf<button type="submit" class="button-primary auth-submit">{{ __('Resend Verification Email') }}</button></form>
    <form method="POST" action="{{ route('logout') }}" class="auth-alternative">@csrf<button type="submit" class="continue-shopping">{{ __('Logout') }}</button></form>
</x-auth-layout>
