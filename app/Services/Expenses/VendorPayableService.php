<?php

namespace App\Services\Expenses;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Illuminate\Database\Eloquent\Collection;

class VendorPayableService
{
    public function createBill(Vendor $vendor, array $data, User $creator): VendorBill
    {
        return VendorBill::create([
            'vendor_id' => $vendor->id,
            'bill_number' => $data['bill_number'] ?? null,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
            'created_by' => $creator->id,
        ]);
    }

    public function recordPayment(Vendor $vendor, array $data, User $creator): VendorPayment
    {
        $bill = null;

        if (! empty($data['vendor_bill_id'])) {
            $bill = VendorBill::where('vendor_id', $vendor->id)->find($data['vendor_bill_id']);

            if ($bill === null) {
                throw new \InvalidArgumentException('That bill was not found for this vendor.');
            }

            if ((float) $data['amount'] > $bill->balance()) {
                throw new \InvalidArgumentException('This payment of ' . $data['amount'] . ' exceeds the bill\'s remaining balance of ' . $bill->balance() . '.');
            }
        }

        return VendorPayment::create([
            'vendor_id' => $vendor->id,
            'vendor_bill_id' => $bill?->id,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'payment_mode' => $data['payment_mode'],
            'description' => $data['description'] ?? null,
            'created_by' => $creator->id,
        ]);
    }

    public function updateBill(VendorBill $bill, array $data): VendorBill
    {
        $bill->fill([
            'bill_number' => $data['bill_number'] ?? null,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
        $bill->save();

        return $bill;
    }

    public function deleteBill(VendorBill $bill): void
    {
        $bill->delete();
    }

    public function updatePayment(VendorPayment $payment, array $data): VendorPayment
    {
        $payment->fill([
            'amount' => $data['amount'],
            'date' => $data['date'],
            'payment_mode' => $data['payment_mode'],
            'description' => $data['description'] ?? null,
        ]);
        $payment->save();

        return $payment;
    }

    public function deletePayment(VendorPayment $payment): void
    {
        $payment->delete();
    }

    public function forDateRange(?string $from, ?string $to): Collection
    {
        $query = VendorPayment::with('vendor');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->where('date', '>=', $from);
        } elseif ($to) {
            $query->where('date', '<=', $to);
        }

        return $query->orderByDesc('date')->get();
    }
}
