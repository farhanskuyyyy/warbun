<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AddressBookService;
use App\Support\DeliveryPoint;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    private function customer(Request $request, ?Customer $customer = null): Customer
    {
        if ($customer) {
            abort_unless($request->user()->is_active && $customer->is_active, 403);
        } else {
            $customer = Customer::where('user_id', $request->user()->id)->where('is_active', true)->firstOrFail();
            abort_unless($request->user()->is_active, 403);
        }

        return $customer;
    }

    public function page(Request $request)
    {
        $customer = $this->customer($request);

        return view('customer.addresses', compact('customer'));
    }

    public function index(Request $request, ?Customer $customer = null)
    {
        return response()->json($this->customer($request, $customer)->addresses()->get());
    }

    public function store(Request $request, ?Customer $customer = null)
    {
        $customer = $this->customer($request, $customer);
        $address = app(AddressBookService::class)->save($customer, $this->validateAddress($request));

        return response()->json($address, 201);
    }

    public function update(Request $request, int $address)
    {
        $customer = $this->customer($request);
        $customer->addresses()->findOrFail($address);

        return response()->json(app(AddressBookService::class)->save($customer, $this->validateAddress($request), $address));
    }

    public function destroy(Request $request, int $address)
    {
        app(AddressBookService::class)->delete($this->customer($request), $address);

        return response()->noContent();
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:80', 'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30', 'address' => 'required|string|max:2000',
            'is_default' => 'sometimes|boolean', ...DeliveryPoint::rules(''),
        ]);
    }
}
