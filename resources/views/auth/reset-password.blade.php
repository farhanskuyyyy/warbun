<x-auth-layout :title="__('Reset Password')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p><h1>{{ __('Choose a new password') }}</h1><p class="auth-form-description">{{ __('Use at least 8 characters.') }}</p>
    <form method="POST" action="{{ route('password.store') }}" class="auth-form">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="field"><label for="field-email">{{ __('Email') }}</label><input id="field-email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
        <x-password-field autocomplete="new-password" />
        <x-password-field name="password_confirmation" :label="__('Confirm Password')" autocomplete="new-password" />
        <button type="submit" class="button-primary auth-submit">{{ __('Reset Password') }}</button>
    </form>
</x-auth-layout>
