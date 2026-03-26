<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\OrderRefundItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderRefundController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.qty' => 'required|integer|min:1',
                'reason' => 'nullable|string',
            ]);

            $order = Order::findOrFail($data['order_id']);

            // Create refund record
            $refund = OrderRefund::create([
                'order_id' => $order->id,
                'reason' => $data['reason'] ?? null,
            ]);

            $totalRefundAmount = 0;

            foreach ($data['items'] as $item) {

                $orderItem = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (! $orderItem) {
                    throw new \Exception('Product not found in order');
                }

                // 🔥 Already returned qty (ALL previous returns)
                $alreadyReturned = DB::table('order_refund_items')
                    ->join('order_refunds', 'order_refunds.id', '=', 'order_refund_items.refund_id')
                    ->where('order_refunds.order_id', $order->id)
                    ->where('order_refund_items.product_id', $item['product_id'])
                    ->sum('order_refund_items.quantity');

                $availableQty = $orderItem->quantity - $alreadyReturned;

                if ($item['qty'] > $availableQty) {
                    throw new \Exception('Return qty exceeds available qty');
                }

                // Save refund item
                OrderRefundItem::create([
                    'refund_id' => $refund->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                ]);

                // ✅ Stock increase
                DB::table('products')
                    ->where('id', $item['product_id'])
                    ->increment('quantity', $item['qty']);

                // ✅ Calculate refund amount
                $totalRefundAmount += $orderItem->price * $item['qty'];
            }

            // 🔥 Correct refund_status logic (per product check)
            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->get();

            $allFullyReturned = true;
            $anyReturned = false;

            foreach ($orderItems as $orderItem) {

                $returnedQty = DB::table('order_refund_items')
                    ->join('order_refunds', 'order_refunds.id', '=', 'order_refund_items.refund_id')
                    ->where('order_refunds.order_id', $order->id)
                    ->where('order_refund_items.product_id', $orderItem->product_id)
                    ->sum('order_refund_items.quantity');

                if ($returnedQty > 0) {
                    $anyReturned = true;
                }

                if ($returnedQty < $orderItem->quantity) {
                    $allFullyReturned = false;
                }
            }

            $refundStatus = null;

            if ($allFullyReturned && $anyReturned) {
                $refundStatus = 'refund';
            } elseif ($anyReturned) {
                $refundStatus = 'partial_refund';
            }

            // Update order
            $order->update([
                'refund_status' => $refundStatus,
            ]);

            // 🔥 Payment history entry (DEBIT)
            DB::table('payment_histories')->insert([
                'date' => now(),
                'payment_name' => 'refund_order_'.$order->id,
                'user_id' => $order->user_id,
                'credit' => 0,
                'debit' => $totalRefundAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Return processed successfully',
                'refund_status' => $refundStatus,
                'refund_amount' => $totalRefundAmount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function list()
    {
        $order = OrderRefund::paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Refund Order fetched successfully',
            'data' => $order,
        ]);
    }
}
