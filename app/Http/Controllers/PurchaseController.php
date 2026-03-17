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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_name' => 'required_if:items.*.product_id,null|string|max:255',
            'items.*.sku' => 'nullable|string|max:255',
            'items.*.product_category_id' => 'nullable|integer|exists:product_categories,id',
            'items.*.product_brand_id' => 'nullable|integer|exists:product_brands,id',
            'items.*.buying_price' => 'nullable|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
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
                        'product_category_id' => $it['product_category_id'] ?? null,
                        'product_brand_id' => $it['product_brand_id'] ?? null,
                        'buying_price' => $it['buying_price'] ?? $price,
                        'selling_price' => $it['selling_price'] ?? $price,
                        'variation_price' => $it['variation_price'] ?? null,
                        'status' => true,
                        'order' => 0,
                        'quantity' => 0,
                        'show_stock_out' => false,
                        'maximum_purchase_quantity' => 1,
                        'low_stock_quantity_warning' => 1,
                        'refundable' => false,
                        'description' => $it['description'] ?? null,
                        'shipping_and_return' => $it['shipping_and_return'] ?? null,
                        'add_to_flash_sale' => false,
                        'discount' => $it['discount'] ?? null,
                        'offer_start_date' => $it['offer_start_date'] ?? null,
                        'offer_end_date' => $it['offer_end_date'] ?? null,
                        'shipping_cost' => $it['shipping_cost'] ?? null,
                        'is_product_quantity_multiply' => false,
                        'barcode_id' => $it['barcode_id'] ?? null,
                    ]);
                }

                // increment stock
                $product->increment('quantity', $qty);

                // store product for second loop
                $products[$key] = $product;

                $subtotal += $price * $qty;
            }

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'total_amount' => $subtotal,
                'purchase_serial_no' => 'PUR'.time().rand(100, 999),
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
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'product_category_id' => $product->product_category_id,
                    'product_brand_id' => $product->product_brand_id,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                    'variation_price' => $product->variation_price,
                    'status' => $product->status,
                    'order' => $product->order,
                    'product_quantity' => $product->quantity,
                    'show_stock_out' => $product->show_stock_out,
                    'maximum_purchase_quantity' => $product->maximum_purchase_quantity,
                    'low_stock_quantity_warning' => $product->low_stock_quantity_warning,
                    'refundable' => $product->refundable,
                    'description' => $product->description,
                    'shipping_and_return' => $product->shipping_and_return,
                    'add_to_flash_sale' => $product->add_to_flash_sale,
                    'discount' => $product->discount,
                    'offer_start_date' => $product->offer_start_date,
                    'offer_end_date' => $product->offer_end_date,
                    'shipping_cost' => $product->shipping_cost,
                    'is_product_quantity_multiply' => $product->is_product_quantity_multiply,
                    'barcode' => $product->barcode,
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
        $purchase = Purchase::where('supplier_id', $id)->with('items    ')->get();

        return response()->json([
            'message' => 'purchase list for supplier',
            'data' => $purchase,
        ], 201);
    }
}
