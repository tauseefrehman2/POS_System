<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Role Types
         *
         */
        $RoleItems = [
            [
                'name' => 'Admin',
                'slug' => 'admin',

            ],
            [
                'name' => 'User',
                'slug' => 'user',

            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',

            ],
            [
                'name' => 'Supplier',
                'slug' => 'supplier',

            ],
            [
                'name' => 'Unverified',
                'slug' => 'unverified',

            ],
        ];

        /*
         * Add Role Items
         *
         */
        foreach ($RoleItems as $RoleItem) {
            $newRoleItem = config('roles.models.role')::where('slug', '=', $RoleItem['slug'])->first();
            if ($newRoleItem === null) {
                $newRoleItem = config('roles.models.role')::create([
                    'name' => $RoleItem['name'],
                    'slug' => $RoleItem['slug'],

                ]);
            }
        }
    }
}
