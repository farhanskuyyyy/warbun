<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashierShift;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;

class PosController extends Controller
{
    public function index()
    {
        $activeShift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->first();
        $recentSales = Sale::where('user_id', auth()->id())->latest()->take(10)->get();
        
        return view('pos.index', compact('activeShift', 'recentSales'));
    }

    public function products(Request $request)
    {
        $query = Product::where('is_active', true)->where('current_stock', '>', 0);
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        $products = $query->orderBy('name')->get();
        
        return response()->json($products);
    }

    public function processSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,transfer,ewallet,debt',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $shift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->first();
        
        if (!$shift) {
            return back()->withErrors(['error' => 'No active cashier shift. Please open a shift first.']);
        }

        \DB::transaction(function () use ($validated, $shift) {
            $subtotal = 0;
            $items = [];
            
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->current_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                
                $itemTotal = $item['unit_price'] * $item['quantity'];
                $subtotal += $itemTotal;
                
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemTotal,
                ];
                
                // Reduce stock
                $previousStock = $product->current_stock;
                $newStock = $previousStock - $item['quantity'];
                $product->update(['current_stock' => $newStock]);
                
                InventoryTransaction::create([
                    'reference_number' => 'POS-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 5, '0', STR_PAD_LEFT),
                    'product_id' => $product->id,
                    'type' => 'stock_out',
                    'source_type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'reason' => 'POS Sale',
                    'user_id' => auth()->id(),
                ]);
            }
            
            $discount = $validated['discount'] ?? 0;
            $total = $subtotal - $discount;
            $paidAmount = $validated['paid_amount'];
            $changeAmount = $paidAmount - $total;
            $debtAmount = $validated['payment_method'] === 'debt' ? $total : 0;
            
            $sale = Sale::create([
                'sale_number' => 'SAL-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 5, '0', STR_PAD_LEFT),
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'cashier_shift_id' => $shift->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => max(0, $changeAmount),
                'debt_amount' => $debtAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);
            
            foreach ($items as $item) {
                $sale->items()->create($item);
            }
            
            // Handle debt
            if ($debtAmount > 0 && $validated['customer_id']) {
                $customer = Customer::findOrFail($validated['customer_id']);
                $customer->update(['outstanding_balance' => $customer->outstanding_balance + $debtAmount]);
                
                if ($customer->debtAccount) {
                    $customer->debtAccount->update([
                        'total_debt' => $customer->debtAccount->total_debt + $debtAmount,
                        'outstanding_balance' => $customer->debtAccount->outstanding_balance + $debtAmount,
                    ]);
                }
            }
            
            AuditLog::log('sale.created', $sale, null, $sale->toArray());
        });

        return redirect()->route('pos.receipt', $sale)->with('success', 'Sale completed!');
    }

    public function receipt(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'user');
        return view('pos.receipt', compact('sale'));
    }

    public function history(Request $request)
    {
        $query = Sale::with('user', 'customer');
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $sales = $query->latest()->paginate(20);
        
        return view('pos.history', compact('sales'));
    }

    public function openShift(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $shift = CashierShift::create([
            'user_id' => auth()->id(),
            'opened_at' => now(),
            'opening_cash' => $validated['opening_cash'],
            'status' => 'active',
        ]);

        AuditLog::log('shift.opened', $shift, null, $shift->toArray());

        return redirect()->route('pos.index')->with('success', 'Shift opened successfully');
    }

    public function closeShift(Request $request)
    {
        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $shift = CashierShift::where('user_id', auth()->id())->where('status', 'active')->firstOrFail();
        
        $salesTotal = $shift->sales()->where('payment_method', 'cash')->sum('paid_amount');
        $expectedCash = $shift->opening_cash + $salesTotal;
        $variance = $validated['closing_cash'] - $expectedCash;

        $shift->update([
            'closed_at' => now(),
            'closing_cash' => $validated['closing_cash'],
            'expected_cash' => $expectedCash,
            'cash_variance' => $variance,
            'status' => 'closed',
        ]);

        AuditLog::log('shift.closed', $shift, null, $shift->toArray());

        return redirect()->route('pos.index')->with('success', 'Shift closed. Variance: Rp ' . number_format($variance, 0, ',', '.'));
    }
}
