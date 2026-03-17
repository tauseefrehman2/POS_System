<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Return all orders for a given user along with payment info.
     */
    public function userOrders($userId)
    {
        $user = User::with(['orders.payment', 'orders.items'])->find($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $orders = $user->orders;

        return response()->json([
            'message' => 'User orders with payment info',
            'data' => $orders,
        ]);
    }

    /**
     * Update payment for a specific order and record history
     */
    public function updatePayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_name' => 'nullable|string',
        ]);

        $userId = $request->user_id;
        $amount = (float) $request->amount;
        $paymentName = $request->payment_name ?? 'Remaining payment';

        DB::beginTransaction();

        try {

            // user ke pending payments find karo
            $payments = Payment::where('user_id', $userId)
                ->where('remaining_amount', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'message' => 'No remaining payment found for this user',
                ], 404);
            }

            $originalAmount = $amount;

            foreach ($payments as $payment) {

                if ($amount <= 0) {
                    break;
                }

                // agar payment remaining se zyada hai
                if ($amount >= $payment->remaining_amount) {

                    $amount -= $payment->remaining_amount;

                    $payment->paid_amount += $payment->remaining_amount;
                    $payment->remaining_amount = 0;

                } else {

                    $payment->paid_amount += $amount;
                    $payment->remaining_amount -= $amount;

                    $amount = 0;
                }

                $payment->save();
            }

            // user remaining update
            $user = User::find($userId);
            if ($user) {
                $user->decrement('remaining_amount', $originalAmount);
            }

            // payment history entry
            PaymentHistory::create([
                'date' => now(),
                'payment_name' => $paymentName,
                'user_id' => $userId,
                'credit' => $originalAmount,
                'debit' => 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment adjusted successfully',
                'paid_amount' => $originalAmount,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
