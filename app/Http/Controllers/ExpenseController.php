<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    /**
     * Display a listing of all expenses.
     */
    public function index()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->get();

        return response()->json($expenses);
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(ExpenseRequest $request)
    {
        try {
            $expense = Expense::create($request->validated());

            return response()->json($expense, 201);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        return response()->json($expense);
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        try {
            $expense->update($request->validated());

            return response()->json($expense);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        try {
            $expense->delete();

            return response()->json(['message' => 'Expense deleted successfully']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }
}
