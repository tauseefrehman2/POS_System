<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelRoles\Models\Role;

class CustomerController extends Controller
{
    protected $user;

    public function index()
    {
        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'customer');
        })->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Customer list fetched successfully',
            'data' => $customers,
        ]);
    }

    // ✅ Create
    public function store(CustomerRequest $request)
    {
        $user = DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'username' => $this->generateUsername($request->email),
                'password' => bcrypt($request->password ?? '12345678'),
                'password_naked' => $request->password ?? '12345678',
                'email_verified_at' => now(),
                'status' => $request->status ?? 1,
            ]);

            $role = Role::where('slug', 'customer')->first();

            if ($role) {
                $user->attachRole($role);
            }

            return $user;
        });

        return response()->json([
            'status' => true,
            'message' => 'Customer created successfully',
            'data' => $user,
        ], 201);
    }

    // ✅ Show
    public function show(User $customer)
    {
        return response()->json([
            'status' => true,
            'message' => 'Customer detail fetched successfully',
            'data' => $customer->load('roles'),
        ]);
    }

    // ✅ Update
    public function update(Request $request, User $customer)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email,'.$customer->id,
            'phone' => 'nullable|string',
            'status' => 'nullable|integer',
            'address' => 'nullable|string',
        ]);

        $customer->update(
            $request->only(['name', 'email', 'phone', 'status', 'address'])
        );

        return response()->json([
            'status' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer->load('roles'),
        ]);
    }

    // ✅ Delete
    public function destroy(User $customer)
    {
        $customer->delete();

        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully',
            'data' => null,
        ]);
    }

    private function generateUsername($email)
    {
        if (! $email) {
            return 'user_'.time();
        }

        return explode('@', $email)[0].rand(100, 999);
    }

    public function filter(Request $request)
    {
        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'customer');
        })
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', $request->id);
            })
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->name.'%');
            })
            ->when($request->email, function ($query) use ($request) {
                $query->where('email', 'like', '%'.$request->email.'%');
            })
            ->when($request->phone, function ($query) use ($request) {
                $query->where('phone', 'like', '%'.$request->phone.'%');
            })
            ->when($request->address, function ($query) use ($request) {
                $query->where('address', 'like', '%'.$request->address.'%');
            });

        $customers = $customers->paginate(10);

        return response()->json([
            'message' => 'Filtered customers',
            'data' => $customers,
        ]);
    }
}
