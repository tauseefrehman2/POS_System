<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoleAndPermission, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'username',
        'password',
        'device_token',
        'web_token',
        'status',
        'country_code',
        'is_guest',
        'address',
        'total_orders',
        'password_naked',
        'balance',
        'remaining_amount',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'email_verified_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'roles',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'is_guest' => 'boolean',
            'account_status' => 'string',
        ];
    }

    /**
     * Payments associated with the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Payment histories for the user.
     */
    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Orders placed by the user.
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * Get user's roles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRolesAttribute()
    {
        return $this->getRoles();
    }

    /**
     * Override level method since we don't use levels in our simplified system.
     *
     * @return int
     */
    public function level()
    {
        return 1; // Default level for all users
    }

    /**
     * Override rolePermissions method to work without level column.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function rolePermissions()
    {
        $permissionModel = app(config('roles.models.permission'));
        $permissionTable = config('roles.permissionsTable');

        if (! $permissionModel instanceof Model) {
            throw new InvalidArgumentException('[roles.models.permission] must be an instance of \Illuminate\Database\Eloquent\Model');
        }

        return $permissionModel::select([$permissionTable.'.*', 'permission_role.created_at as pivot_created_at', 'permission_role.updated_at as pivot_updated_at'])
            ->join('permission_role', 'permission_role.permission_id', '=', $permissionTable.'.id')
            ->join(config('roles.rolesTable'), config('roles.rolesTable').'.id', '=', 'permission_role.role_id')
            ->whereNull(config('roles.rolesTable').'.deleted_at')
            ->whereIn(config('roles.rolesTable').'.id', $this->getRoles()->pluck('id')->toArray())
            ->groupBy([$permissionTable.'.id', $permissionTable.'.name', $permissionTable.'.slug', $permissionTable.'.created_at', $permissionTable.'.updated_at', $permissionTable.'.deleted_at', 'pivot_created_at', 'pivot_updated_at']);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id');
    }

    public function SupplierpaymentHistories()
    {
        return $this->hasMany(SupplierPaymentHistory::class);
    }
}
