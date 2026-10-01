<x-auth-layout :title="__('Register')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p>
    <h1>{{ __('Create account') }}</h1>
    <p class="auth-form-description">{{ __('Save your orders in one place. Your cart stays with you while you sign up.') }}</p>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="field"><label for="field-name">{{ __('Name') }}</label><input id="field-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="error-name" @enderror>@error('name')<p id="error-name" class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field"><label for="field-email">{{ __('Email') }}</label><input id="field-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>@error('email')<p id="error-email" class="field-error">{{ $message }}</p>@enderror</div>
        <x-password-field autocomplete="new-password" />
        <p class="auth-field-help">{{ __('Use at least 8 characters.') }}</p>
        <x-password-field name="password_confirmation" :label="__('Confirm Password')" autocomplete="new-password" />
        <button type="submit" class="button-primary auth-submit">{{ __('Create account') }}</button>
    </form>
    <p class="auth-alternative">{{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Sign in') }}</a></p>
</x-auth-layout>
