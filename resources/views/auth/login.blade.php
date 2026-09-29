<x-auth-layout title="Login">
    <h2 class="text-xl font-semibold text-gray-800 mb-1">Welcome back</h2>
    <p class="text-sm text-gray-500 mb-6">Sign in to your account</p>
    
    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition @error('email') border-red-500 @enderror"
                    placeholder="admin@warbun.local">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    placeholder="••••••••">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="text-sm text-gray-600">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary-dark">Forgot password?</a>
                @endif
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition active:scale-[0.98]">
                Sign In
            </button>
        </div>
    </form>
    
    @if (Route::has('register'))
        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary font-medium hover:text-primary-dark">Register</a>
        </p>
    @endif
</x-auth-layout>
