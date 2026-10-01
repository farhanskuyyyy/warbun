@section('title', __('Profile'))
<x-app-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
