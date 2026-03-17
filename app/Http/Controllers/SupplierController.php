<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    protected $user;

    public function index()
    {
        $suppliers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'supplier');
        })->get();

        return response()->json($suppliers);
    }

    public function store(SupplierRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'username' => $this->generateUsername($request->email),
                    'password' => bcrypt($request->password),
                    'email_verified_at' => now(),
                    'status' => $request->status ?? 1,
                    'country_code' => $request->country_code,
                    'is_guest' => 0,
                ]);
                $this->user->assignRole('supplier');
            });

            return response()->json($this->user, 201);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    public function show(User $supplier)
    {
        return response()->json($supplier->load('roles'));
    }

    public function update(Request $request, User $supplier)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email,'.$supplier->id,
            'phone' => 'nullable|string',
            'status' => 'nullable|integer',
            'country_code' => 'nullable|string',
        ]);

        $supplier->update($request->only(['name', 'email', 'phone', 'status', 'country_code']));

        return response()->json($supplier->load('roles'));
    }

    public function destroy(User $supplier)
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted']);
    }

    private function generateUsername($email)
    {
        if (! $email) {
            return 'supplier_'.time();
        }

        return explode('@', $email)[0].rand(100, 999);
    }

    public function supplierSearch(Request $request)
    {
        $search = $request->search;

        $suppliers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'supplier');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%");
                });
            })->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Suppliers fetched successfully',
            'data' => $suppliers,
        ]);
    }
}
