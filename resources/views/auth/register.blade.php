<x-auth-layout title="Register">
    <h2 class="text-xl font-semibold text-gray-800 mb-1">Create account</h2>
    <p class="text-sm text-gray-500 mb-6">Fill in the details below</p>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition @error('name') border-red-500 @enderror"
                    placeholder="Your name">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition @error('email') border-red-500 @enderror"
                    placeholder="you@example.com">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    placeholder="••••••••">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                <input type="password" name="password_confirmation" required
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
        <a href="{{ route('login') }}" class="text-primary font-medium hover:text-primary-dark">Sign in</a>
    </p>
</x-auth-layout>
