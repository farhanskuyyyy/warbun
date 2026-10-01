<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CashierShift;
use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        \DB::transaction(function () use ($request) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $v = $request->validated();
            $user->fill(Arr::only($v, ['name', 'email']));
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
            $user->customer()->lockForUpdate()->first()?->update(Arr::only($v, ['name', 'email', 'phone', 'address']));
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if (CashierShift::where('user_id', $user->id)->exists() || InventoryTransaction::where('user_id', $user->id)->exists() || Refund::where('user_id', $user->id)->exists() || StockOpname::where('user_id', $user->id)->orWhere('approved_by', $user->id)->exists() || $user->sales()->exists() || Payment::where('user_id', $user->id)->exists() || $user->customer?->orders()->exists() || $user->customer?->debtTransactions()->exists()) {
            $user->update(['is_active' => false]);
        } else {
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
