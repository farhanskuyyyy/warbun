<form action="{{ route('locale.update') }}" method="POST" class="locale-switcher">
    @csrf
    <label for="locale" title="{{ __('Language') }}"><x-icon name="language" /><span class="sr-only">{{ __('Language') }}</span></label>
    <select id="locale" name="locale"><option value="id" @selected(app()->getLocale()==='id')>Indonesia</option><option value="en" @selected(app()->getLocale()==='en')>English</option></select>
    <button type="submit" class="icon-button" aria-label="{{ __('Apply language') }}" title="{{ __('Apply language') }}"><x-icon name="check" /></button>
</form>
