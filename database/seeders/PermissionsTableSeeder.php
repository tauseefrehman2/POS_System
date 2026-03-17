<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Permission Types
         *
         */
        $Permissionitems = [
            [
                'name' => 'Can View Users',
                'slug' => 'view.users',

            ],
            [
                'name' => 'Can Create Users',
                'slug' => 'create.users',

            ],
            [
                'name' => 'Can Edit Users',
                'slug' => 'edit.users',

            ],
            [
                'name' => 'Can Delete Users',
                'slug' => 'delete.users',

            ],
        ];

        /*
         * Add Permission Items
         *
         */
        foreach ($Permissionitems as $Permissionitem) {
            $newPermissionitem = config('roles.models.permission')::where('slug', '=', $Permissionitem['slug'])->first();
            if ($newPermissionitem === null) {
                $newPermissionitem = config('roles.models.permission')::create([
                    'name' => $Permissionitem['name'],
                    'slug' => $Permissionitem['slug'],

                ]);
            }
        }
    }
}
