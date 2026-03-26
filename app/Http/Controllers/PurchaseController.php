<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|integer|exists:users,id',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_name' => 'required_if:items.*.product_id,null|string|max:255',
        ]);

        $purchase = DB::transaction(function () use ($request) {

            $subtotal = 0;
            $products = [];

            foreach ($request->items as $key => $it) {

                $price = (float) $it['price'];
                $qty = (int) $it['quantity'];

                if (! empty($it['product_id'])) {
                    $product = Product::findOrFail($it['product_id']);
                } else {
                    $product = Product::create([
                        'name' => $it['product_name'],
                        'slug' => Str::slug($it['product_name']),
                        'sku' => $it['sku'] ?? null,
                        'buying_price' => $price,
                        'selling_price' => $price,
                        'quantity' => 0,
                    ]);
                }

                $product->increment('quantity', $qty);

                $products[$key] = $product;

                $subtotal += $price * $qty;
            }

            // ✅ payment logic
            $paidAmount = $request->paid_amount ?? 0;
            $remaining = $subtotal - $paidAmount;

            if ($paidAmount == 0) {
                $paymentStatus = 'unpaid';
            } elseif ($paidAmount >= $subtotal) {
                $paymentStatus = 'paid';
                $remaining = 0;
            } else {
                $paymentStatus = 'partial';
            }

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'total_amount' => $subtotal,
                'paid_amount' => $paidAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $request->payment_method,
                'purchase_serial_no' => 'PUR'.time().rand(100, 999),
            ]);

            // ✅ add ONLY remaining to supplier
            if ($remaining > 0) {
                DB::table('users')
                    ->where('id', $request->supplier_id)
                    ->increment('remaining_amount', $remaining);
            }

            // ✅ payment history
            DB::table('supplier_payment_histories')->insert([
                'date' => now(),
                'payment_name' => 'purchase_'.$purchase->id,
                'supplier_id' => $request->supplier_id,
                'credit' => $paidAmount, // paid
                'debit' => $remaining,   // remaining
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->items as $key => $it) {

                $product = $products[$key];
                $price = (float) $it['price'];
                $qty = (int) $it['quantity'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $price * $qty,
                    'product_name' => $product->name,
                ]);
            }

            return $purchase;
        });

        return response()->json([
            'message' => 'Purchase created successfully',
            'data' => $purchase->load('items'),
        ], 201);
    }

    public function index($id)
    {
        $purchase = Purchase::where('supplier_id', $id)->with('items')->get();

        return response()->json([
            'message' => 'purchase list for supplier',
            'data' => $purchase,
        ], 201);
    }
}
