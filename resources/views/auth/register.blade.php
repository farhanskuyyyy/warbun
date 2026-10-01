<x-auth-layout title="{{ __('Register') }}">
    <h2 class="text-xl font-semibold text-gray-800 mb-1">{{ __('Create account') }}</h2>
    <p class="text-sm text-gray-500 mb-6">{{ __('Fill in the details below') }}</p>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label for="field-name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Name') }}</label>
                <input id="field-name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition @error('name') border-red-500 @enderror"
                    placeholder="{{ __('Your name') }}">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="field-email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Email') }}</label>
                <input id="field-email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition @error('email') border-red-500 @enderror"
                    placeholder="you@example.com">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="field-password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Password') }}</label>
                <input id="field-password" type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    placeholder="••••••••">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="field-password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Confirm Password') }}</label>
                <input id="field-password_confirmation" type="password" name="password_confirmation" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    placeholder="••••••••">
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition active:scale-[0.98]">
                Create Account
            </button>
        </div>
    </form>
    
    <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account? 
        <a href="{{ route('login') }}" class="text-primary font-medium hover:text-primary-dark">{{ __('Sign in') }}</a>
    </p>
</x-auth-layout>
