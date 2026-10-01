@props(['name' => 'password', 'label' => __('Password'), 'autocomplete' => 'current-password'])
<div class="field">
    <label for="field-{{ $name }}">{{ $label }}</label>
    <div class="password-control">
        <input id="field-{{ $name }}" type="password" name="{{ $name }}" required autocomplete="{{ $autocomplete }}" @error($name) aria-invalid="true" aria-describedby="error-{{ $name }}" @enderror>
        <button type="button" data-password-toggle="field-{{ $name }}" data-show="{{ __('Show password') }}" data-hide="{{ __('Hide password') }}" data-show-text="{{ __('Show') }}" data-hide-text="{{ __('Hide') }}" aria-label="{{ __('Show password') }}" aria-controls="field-{{ $name }}" aria-pressed="false">{{ __('Show') }}</button>
    </div>
    @error($name)<p id="error-{{ $name }}" class="field-error">{{ $message }}</p>@enderror
</div>
