<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddressBookService
{
    public function save(Customer $customer, array $data, ?int $id = null): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $data, $id) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);
            $address = $id ? $customer->addresses()->lockForUpdate()->findOrFail($id) : new CustomerAddress;
            $old = $address->exists ? $address->toArray() : null;
            $default = ($data['is_default'] ?? false) || $address->is_default || ! $customer->addresses()->exists();
            if ($default) {
                $customer->addresses()->where('id', '!=', $address->id ?? 0)->update(['is_default' => false]);
            }
            $address->fill(array_merge($data, ['is_default' => $default]));
            $address->customer_id = $customer->id;
            $address->save();
            $this->syncDefault($customer);
            AuditLog::log($old ? 'address.updated' : 'address.created', $address, $old, $address->toArray());

            return $address;
        }, 3);
    }

    public function delete(Customer $customer, int $id): void
    {
        DB::transaction(function () use ($customer, $id) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);
            $address = $customer->addresses()->lockForUpdate()->findOrFail($id);
            $old = $address->toArray();
            $address->delete();
            if ($address->is_default && ($next = $customer->addresses()->first())) {
                $next->update(['is_default' => true]);
            }
            $this->syncDefault($customer);
            AuditLog::log('address.deleted', $address, $old, null);
        }, 3);
    }

    private function syncDefault(Customer $customer): void
    {
        $default = $customer->addresses()->where('is_default', true)->first();
        $customer->update(['address' => $default?->address, 'latitude' => $default?->latitude, 'longitude' => $default?->longitude]);
    }

    public function shipping(Customer $customer, mixed $id): array
    {
        $address = $customer->addresses()->lockForUpdate()->find($id);
        if (! $address) {
            throw ValidationException::withMessages(['address_id' => __('This address is unavailable. Choose another address.')]);
        }

        return ['address' => $address->shippingText(), 'shipping_address' => $address->shippingText(),
            'delivery_phone' => $address->phone,
            'shipping_latitude' => $address->latitude, 'shipping_longitude' => $address->longitude];
    }

    public function importLegacy(Customer $customer): void
    {
        if (trim($customer->address ?? '') !== '' && ! $customer->addresses()->exists()) {
            $this->save($customer, ['label' => __('Main address'), 'recipient_name' => $customer->name,
                'phone' => $customer->phone, 'address' => $customer->address,
                'latitude' => $customer->latitude, 'longitude' => $customer->longitude, 'is_default' => true]);
        }
    }
}
