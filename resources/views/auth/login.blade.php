<x-auth-layout :title="__('Login')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p>
    <h1>{{ __('Welcome back') }}</h1>
    <p class="auth-form-description">{{ session('url.intended') ? __('Sign in to continue your shopping. Your cart is saved in this browser.') : __('Sign in to see your orders or manage the store.') }}</p>
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div class="field">
            <label for="field-email">{{ __('Email or phone') }}</label>
            <input id="field-email" type="text" name="email" value="{{ old('email') }}" required autocomplete="username" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>
            @error('email')<p id="error-email" class="field-error">{{ $message }}</p>@enderror
        </div>
        <x-password-field />
        <div class="auth-options"><label><input type="checkbox" name="remember">{{ __('Remember me') }}</label><a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a></div>
        <button type="submit" class="button-primary auth-submit">{{ __('Sign in') }}</button>
    </form>
    <p class="auth-alternative">{{ __('New to Warbun?') }} <a href="{{ route('register') }}">{{ __('Create account') }}</a></p>
</x-auth-layout>
