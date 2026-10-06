<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DebtAccount;
use App\Models\User;
use App\Services\AddressBookService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('debtAccount');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->has_debt === 'yes') {
            $query->where('outstanding_balance', '>', 0);
        }

        $customers = $query->latest()->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id|unique:customers,user_id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string',
            'can_use_debt' => 'boolean',
            'credit_limit' => 'required_if:can_use_debt,1|nullable|numeric|min:0',
        ]);

        $validated['is_active'] = true;
        $validated['outstanding_balance'] = 0;
        $validated['debt_status'] = $request->boolean('can_use_debt') ? 'eligible' : 'restricted';

        if ($request->boolean('can_use_debt') && empty($validated['user_id'])) {
            throw ValidationException::withMessages(['user_id' => __('Registered eligible customer required.')]);
        }
        $customer = \DB::transaction(function () use ($validated, $request) {
            if (! empty($validated['user_id'])) {
                User::lockForUpdate()->findOrFail($validated['user_id']);
                if (Customer::where('user_id', $validated['user_id'])->exists()) {
                    throw ValidationException::withMessages(['user_id' => __('Account already linked.')]);
                }
            }
            $customer = Customer::create($validated);
            app(AddressBookService::class)->importLegacy($customer);

            if ($request->boolean('can_use_debt')) {
                DebtAccount::firstOrCreate([
                    'customer_id' => $customer->id], [
                        'credit_limit' => $validated['credit_limit'] ?? 0,
                    ]);
            }

            AuditLog::log('customer.created', $customer, null, $customer->toArray());

            return $customer;
        });

        return redirect()->route('customers.index')->with('success', __('Customer created successfully'));
    }

    public function show(Customer $customer)
    {
        $customer->load('debtAccount', 'sales', 'debtTransactions');

        $recentSales = $customer->sales()->latest()->take(10)->get();
        $debtTransactions = $customer->debtTransactions()->latest()->take(10)->get();

        return view('customers.show', compact('customer', 'recentSales', 'debtTransactions'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone,'.$customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,'.$customer->id,
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'can_use_debt' => 'boolean',
            'credit_limit' => 'nullable|numeric|min:0',
            'debt_status' => 'in:eligible,restricted,suspended,blocked',
        ]);

        \DB::transaction(function () use ($validated, $customer, $request) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);
            $validated['is_active'] = $request->boolean('is_active');
            $validated['can_use_debt'] = $request->boolean('can_use_debt');
            $oldValues = $customer->toArray();
            if (array_key_exists('address', $validated) && $validated['address'] !== $customer->address) {
                $validated['latitude'] = null;
                $validated['longitude'] = null;
            }
            $validated['debt_status'] = $request->debt_status ?? $customer->debt_status;

            if ($request->boolean('can_use_debt') && ! $customer->user_id) {
                throw ValidationException::withMessages(['user_id' => __('Registered eligible customer required.')]);
            }
            $customer->update($validated);
            if (array_key_exists('address', $validated) && $validated['address'] !== ($oldValues['address'] ?? null)) {
                $default = $customer->addresses()->where('is_default', true)->first();
                if (trim($customer->address ?? '') !== '') {
                    app(AddressBookService::class)->save($customer, [
                        'label' => $default?->label ?? __('Main address'), 'recipient_name' => $default?->recipient_name ?? $customer->name,
                        'phone' => $default?->phone ?? $customer->phone, 'address' => $customer->address,
                        'latitude' => null, 'longitude' => null, 'is_default' => true,
                    ], $default?->id);
                } elseif ($default) {
                    app(AddressBookService::class)->delete($customer, $default->id);
                }
            }

            if ($customer->can_use_debt && ! $customer->debtAccount) {
                DebtAccount::create(['customer_id' => $customer->id, 'credit_limit' => $customer->credit_limit]);
            }
            if ($customer->debtAccount) {
                $customer->debtAccount->update([
                    'credit_limit' => $validated['credit_limit'] ?? $customer->debtAccount->credit_limit,
                ]);
            }

            AuditLog::log('customer.updated', $customer, $oldValues, $customer->toArray());

        });

        return redirect()->route('customers.index')->with('success', __('Customer updated successfully'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->outstanding_balance > 0) {
            return back()->withErrors(['error' => __('Cannot delete customer with outstanding debt')]);
        }

        $oldValues = $customer->toArray();
        $customer->delete();

        AuditLog::log('customer.deleted', null, $oldValues, null);

        return redirect()->route('customers.index')->with('success', __('Customer deleted successfully'));
    }
}
