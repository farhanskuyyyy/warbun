<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:customers,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);
            Role::findOrCreate('customer', 'web');
            $user->assignRole('customer');
            Customer::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $request->phone, 'can_use_debt' => false, 'debt_status' => 'restricted']);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('customer.history', absolute: false));
    }
}
