<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

class OrderController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'items' => 'required|array|min:1',
    //         'items.*.product_id' => 'required|integer|exists:products,id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'items.*.price' => 'nullable|numeric|min:0',
    //         'order_datetime' => 'nullable|date',
    //         'delivery_datetime' => 'nullable|date',
    //         'payment_method' => 'nullable|string',
    //         'payment_status' => 'nullable|integer',
    //         'status' => 'nullable|integer',
    //         'user_id' => 'nullable|integer|exists:users,id',
    //         'user_name' => 'nullable|string|max:255',
    //         'user_email' => 'nullable|email|max:255',
    //         'password' => 'nullable|min:6',
    //     ]);

    //     $order = DB::transaction(function () use ($request) {
    //         $userId = $request->input('user_id');

    //         if (! $userId) {

    //             if ($request->user_name && $request->user_email) {

    //                 $request->validate([
    //                     'user_name' => 'required|string|max:255',
    //                     'user_email' => 'required|email|max:255|unique:users,email',
    //                     'password' => 'required|min:6',
    //                 ]);

    //                 $user = User::create([
    //                     'name' => $request->user_name,
    //                     'email' => $request->user_email,
    //                     'password' => bcrypt($request->password),
    //                     'is_guest' => true,
    //                 ]);

    //                 $customerRole = Role::where('name', 'Customer')->first();
    //                 if ($customerRole) {
    //                     $user->roles()->attach($customerRole->id);
    //                 }

    //                 $userId = $user->id;

    //             } else {

    //                 // Anonymous user
    //                 $user = User::create([
    //                     'name' => 'Anonymous',
    //                     'is_guest' => true,
    //                 ]);

    //                 $userId = $user->id;
    //             }
    //         }

    //         $subtotal = 0;

    //         foreach ($request->items as $it) {

    //             $product = Product::findOrFail($it['product_id']);

    //             $price = $it['price'] ?? $product->selling_price;
    //             $qty = (int) $it['quantity'];

    //             if ($qty > $product->quantity) {
    //                 abort(400, "Insufficient stock for product id {$product->id}");
    //             }

    //             $subtotal += $price * $qty;
    //         }

    //         $discount = $request->input('discount', 0);
    //         $tax = $request->input('tax', 0);
    //         $shipping = $request->input('shipping_charge', 0);

    //         $total = $subtotal - $discount + $tax + $shipping;

    //         $order = Order::create([
    //             'cashier_id' => auth()->id(),
    //             'order_serial_no' => 'ORD'.time().rand(100, 999),
    //             'user_id' => $userId,
    //             'subtotal' => $subtotal,
    //             'tax' => $tax,
    //             'discount' => $discount,
    //             'shipping_charge' => $shipping,
    //             'total' => $total,
    //             'order_type' => $request->input('order_type', 1),
    //             'special_instructions' => $request->input('special_instructions'),
    //             'payment_method' => $request->input('payment_method'),
    //             'payment_status' => $request->input('payment_status', 0),
    //             'status' => $request->input('status', 0),
    //             'active' => $request->input('active', 1),
    //             'reason' => $request->input('reason'),
    //             'source' => $request->input('source'),
    //         ]);

    //         foreach ($request->items as $it) {

    //             $product = Product::findOrFail($it['product_id']);
    //             $price = $it['price'] ?? $product->selling_price;
    //             $qty = (int) $it['quantity'];

    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'product_id' => $product->id,
    //                 'quantity' => $qty,
    //                 'price' => $price,
    //                 'total' => $price * $qty,
    //                 'product_name' => $product->name,
    //                 'slug' => $product->slug,
    //                 'sku' => $product->sku,
    //                 'product_category_id' => $product->product_category_id,
    //                 'product_brand_id' => $product->product_brand_id,
    //                 'buying_price' => $product->buying_price,
    //                 'selling_price' => $product->selling_price,
    //                 'variation_price' => $product->variation_price,
    //                 'status' => $product->status,
    //                 'order' => $product->order,
    //                 'product_stock' => $product->quantity,
    //                 'show_stock_out' => $product->show_stock_out,
    //                 'maximum_purchase_quantity' => $product->maximum_purchase_quantity,
    //                 'low_stock_quantity_warning' => $product->low_stock_quantity_warning,
    //                 'weight' => $product->weight,
    //                 'refundable' => $product->refundable,
    //                 'description' => $product->description,
    //                 'shipping_and_return' => $product->shipping_and_return,
    //                 'add_to_flash_sale' => $product->add_to_flash_sale,
    //                 'discount' => $product->discount,
    //                 'offer_start_date' => $product->offer_start_date,
    //                 'offer_end_date' => $product->offer_end_date,
    //                 'shipping_type' => $product->shipping_type,
    //                 'shipping_cost' => $product->shipping_cost,
    //                 'is_product_quantity_multiply' => $product->is_product_quantity_multiply,
    //                 'barcode' => $product->barcode,
    //             ]);

    //             $product->decrement('quantity', $qty);
    //         }

    //         $user = User::find($userId);
    //         if ($user) {
    //             $user->increment('total_orders');
    //         }

    //         $paidAmount = (float) $request->input('paid_amount', 0);
    //         $remaining = $order->total - $paidAmount;

    //         Payment::create([
    //             'user_id' => $userId,
    //             'order_id' => $order->id,
    //             'paid_amount' => $paidAmount,
    //             'remaining_amount' => $remaining,
    //         ]);

    //         if ($user) {
    //             $user->increment('remaining_amount', $remaining);
    //         }

    //         PaymentHistory::create([
    //             'date' => now(),
    //             'payment_name' => 'Order #'.$order->id,
    //             'user_id' => $userId,
    //             'credit' => $paidAmount,
    //             'debit' => $order->total,
    //         ]);

    //         return $order;
    //     });

    //     return response()->json([
    //         'message' => 'Order created successfully',
    //         'data' => $order->load('items'),
    //     ], 201);
    // }
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|integer|exists:users,id',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
            'password' => 'nullable|min:6',
        ]);

        $orderedProductsStock = [];

        $order = DB::transaction(function () use ($request, &$orderedProductsStock) {

            $userId = $request->input('user_id');

            /*
            -------------------------------
            USER HANDLE
            -------------------------------
            */

            if (! $userId) {

                if ($request->user_name && $request->user_email) {

                    $request->validate([
                        'user_name' => 'required|string|max:255',
                        'user_email' => 'required|email|max:255|unique:users,email',
                        'password' => 'required|min:6',
                    ]);

                    $user = User::create([
                        'name' => $request->user_name,
                        'email' => $request->user_email,
                        'password' => bcrypt($request->password),
                        'is_guest' => true,
                    ]);

                    $customerRole = Role::where('name', 'Customer')->first();
                    if ($customerRole) {
                        $user->roles()->attach($customerRole->id);
                    }

                    $userId = $user->id;

                } else {

                    $user = User::create([
                        'name' => 'Anonymous',
                        'is_guest' => true,
                    ]);

                    $userId = $user->id;
                }
            }

            /*
            -------------------------------
            CALCULATE TOTAL
            -------------------------------
            */

            $subtotal = 0;

            foreach ($request->items as $it) {

                $product = Product::findOrFail($it['product_id']);
                $price = $it['price'] ?? $product->selling_price;
                $qty = (int) $it['quantity'];

                if ($qty > $product->quantity) {
                    abort(400, "Insufficient stock for product id {$product->id}");
                }

                $subtotal += $price * $qty;
            }

            $discount = $request->input('discount', 0);
            $tax = $request->input('tax', 0);
            $shipping = $request->input('shipping_charge', 0);

            $total = $subtotal - $discount + $tax + $shipping;

            $originalPaidAmount = (float) $request->input('paid_amount', 0);

            /*
            -------------------------------
            PAYMENT CALCULATION
            -------------------------------
            */

            $paidAmount = $originalPaidAmount;
            $remainingAmount = $total - $paidAmount;
            $extraAmount = 0;

            if ($paidAmount > $total) {

                $extraAmount = $paidAmount - $total;
                $paidAmount = $total;
                $remainingAmount = 0;
            }

            /*
            -------------------------------
            PAYMENT STATUS
            -------------------------------
            */

            $paymentStatus = 'unpaid';

            if ($originalPaidAmount > 0 && $originalPaidAmount < $total) {
                $paymentStatus = 'partial';
            }

            if ($originalPaidAmount >= $total) {
                $paymentStatus = 'paid';
            }

            /*
            -------------------------------
            CREATE ORDER
            -------------------------------
            */

            $order = Order::create([
                'cashier_id' => auth()->id(),
                'order_serial_no' => 'ORD'.time().rand(100, 999),
                'user_id' => $userId,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'shipping_charge' => $shipping,
                'total' => $total,
                'payment_status' => $paymentStatus,
                'status' => $request->input('status', 0),
            ]);

            /*
            -------------------------------
            ORDER ITEMS + STOCK UPDATE
            -------------------------------
            */

            foreach ($request->items as $it) {

                $product = Product::findOrFail($it['product_id']);
                $price = $it['price'] ?? $product->selling_price;
                $qty = (int) $it['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $price * $qty,
                    'product_name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                ]);

                $product->decrement('quantity', $qty);

                // refresh product to get new quantity
                $product->refresh();

                $orderedProductsStock[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'ordered_quantity' => $qty,
                    'remaining_quantity' => $product->quantity,
                ];
            }

            $user = User::find($userId);

            if ($user) {
                $user->increment('total_orders');
            }

            /*
            -------------------------------
            PAYMENT ENTRY
            -------------------------------
            */

            Payment::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
            ]);

            /*
            -------------------------------
            USER REMAINING UPDATE
            -------------------------------
            */

            if ($user) {

                if ($remainingAmount > 0) {
                    $user->increment('remaining_amount', $remainingAmount);
                }

                if ($extraAmount > 0) {
                    $user->decrement('remaining_amount', $extraAmount);
                }
            }

            /*
            -------------------------------
            PAYMENT HISTORY
            -------------------------------
            */

            PaymentHistory::create([
                'date' => now(),
                'payment_name' => 'Order #'.$order->id,
                'user_id' => $userId,
                'credit' => $originalPaidAmount,
                'debit' => $total,
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items'),
            'products_stock' => $orderedProductsStock,
        ], 201);
    }

    public function index()
    {
        $orders = Order::with('items')->paginate(10);

        return response()->json([
            'message' => 'Order Fetched Successfully',
            'data' => $orders,
        ], 201);
    }
}
