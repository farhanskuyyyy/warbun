<details class="account-menu">
    <summary aria-label="{{ __('Account') }}" title="{{ __('Account') }}"><span class="account-avatar"><x-icon name="user" /></span><span>{{ __('Account') }}</span><x-icon name="chevron-down" class="disclosure-icon" /></summary>
    <div class="account-popover">
        @auth
            <p class="account-name">{{ auth()->user()->name }}</p>
            <a href="{{ route('profile.edit') }}"><x-icon name="user" />{{ __('Profile') }}</a>
            @can('dashboard.view')<a href="{{ route('dashboard') }}"><x-icon name="gauge-high" />{{ __('Dashboard') }}</a>@endcan
            @if(auth()->user()->customer)<a href="{{ route('customer.history') }}"><x-icon name="bag-shopping" />{{ __('My orders') }}</a>@endif
            @if(auth()->user()->customer)<a href="{{ route('customer.addresses') }}"><x-icon name="truck" />{{ __('My addresses') }}</a>@endif
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="right-from-bracket" />{{ __('Logout') }}</button></form>
        @else
            <a href="{{ route('login') }}"><x-icon name="right-to-bracket" />{{ __('Login') }}</a>
            <a href="{{ route('register') }}"><x-icon name="user-plus" />{{ __('Register') }}</a>
        @endauth
        <div class="language-settings">@include('components.locale-switcher')</div>
    </div>
</details>
