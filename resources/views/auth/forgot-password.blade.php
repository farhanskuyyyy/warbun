<x-auth-layout :title="__('Forgot password?')">
    <p class="eyebrow">{{ __('Your Warbun account') }}</p><h1>{{ __('Reset your password') }}</h1>
    <p class="auth-form-description">{{ __('Enter your account email. We will send you a link to choose a new password.') }}</p>
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">@csrf
        <div class="field"><label for="field-email">{{ __('Email') }}</label><input id="field-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="error-email" @enderror>@error('email')<p id="error-email" class="field-error">{{ $message }}</p>@enderror</div>
        <button type="submit" class="button-primary auth-submit">{{ __('Email Password Reset Link') }}</button>
    </form>
    <p class="auth-alternative"><a href="{{ route('login') }}">{{ __('Back to sign in') }}</a></p>
</x-auth-layout>
