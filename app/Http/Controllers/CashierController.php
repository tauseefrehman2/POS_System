<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashierRequest;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

class CashierController extends Controller
{
    protected $user;

    public function index(Request $request)
    {
        $search = $request->search;

        $cashiers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'cashier');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%");
                });
            })
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Cashiers fetched successfully',
            'data' => $cashiers,
        ]);
    }

    public function store(CashierRequest $request)
    {
        // dd($request->all());
        $this->user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'username' => $this->generateUsername($request->email),
                'password' => bcrypt($request->password ?? '12345678'),
                'password_naked' => $request->password,
                'email_verified_at' => now(),
                'status' => $request->status ?? 1,
            ]);

            $role = Role::where('slug', 'cashier')->first();
            $user->attachRole($role);

            return $user;
        });

        return response()->json($this->user, 201);
    }

    public function show(User $cashier)
    {
        return response()->json($cashier->load('roles'));
    }

    public function update(Request $request, User $cashier)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email,'.$cashier->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|integer',
        ]);

        $cashier->update($request->only(['name', 'email', 'phone', 'status', 'address']));

        return response()->json($cashier->load('roles'));
    }

    public function destroy(User $cashier)
    {
        $cashier->delete();

        return response()->json(['message' => 'Cashier deleted']);
    }

    private function generateUsername($email)
    {
        if (! $email) {
            return 'cashier_'.time();
        }

        return explode('@', $email)[0].rand(100, 999);
    }

    public function todayOrders()
    {
        $cashierId = auth()->id();

        $orders = \App\Models\Order::where('cashier_id', $cashierId)
            ->whereDate('created_at', today())
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Today orders fetched successfully',
            'data' => $orders,
        ], 200);
    }

    public function allUser(Request $request)
    {
        $search = $request->search;

        $cashiers = User::whereHas('roles', function ($q) {
            $q->where('slug', '!=', 'customer')
                ->where('slug', '!=', 'supplier');
        })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('id', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%");
                });
            })->where('name', '!=', 'Anonymous')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Cashiers fetched successfully',
            'data' => $cashiers,
        ]);
    }

    public function cashierOrders(Request $request, $cashier_id)
    {
        $filter = $request->filter;

        $query = Order::where('cashier_id', $cashier_id);

        if ($filter == 'last_month') {
            $query->whereBetween('created_at', [
                Carbon::now()->subMonth(),
                Carbon::now(),
            ]);
        }

        if ($filter == 'last_3_months') {
            $query->whereBetween('created_at', [
                Carbon::now()->subMonths(3),
                Carbon::now(),
            ]);
        }

        if ($filter == 'last_year') {
            $query->whereBetween('created_at', [
                Carbon::now()->subYear(),
                Carbon::now(),
            ]);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Cashier orders fetched successfully',
            'data' => $orders,
        ]);
    }
}
