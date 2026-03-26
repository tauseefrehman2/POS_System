<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\Models\SupplierPaymentHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $supplier = User::findOrFail($request->supplier_id);

            // ❗ prevent over payment
            if ($request->amount > $supplier->remaining_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment exceeds remaining amount',
                ], 400);
            }

            // ✅ create payment
            $payment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            // ✅ decrease remaining amount
            $supplier->decrement('remaining_amount', $request->amount);

            // ✅ Supplier Ledger Entry (CREDIT 🔥)
            DB::table('supplier_payment_histories')->insert([
                'date' => now(),
                'payment_name' => 'supplier_payment_'.$payment->id,
                'supplier_id' => $supplier->id,
                'credit' => $request->amount, // paisa diya
                'debit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment added successfully',
                'data' => $payment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $payments = SupplierPaymentHistory::when($request->id, function ($q) use ($request) {
            $q->where('supplier_id', $request->id);
        })
            ->latest()
            ->paginate(10);

        if ($payments->total() == 0) {
            return response()->json([
                'status' => true,
                'message' => 'No supplier payment history found',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Supplier payment history',
            'data' => $payments,
        ]);
    }
}
