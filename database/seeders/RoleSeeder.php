<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Gunakan firstOrCreate agar tidak error jika role sudah ada
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user  = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Begitu juga dengan permission
        $permissions = [
            'approve devices',
            'manage users',
            'view logs'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Sinkronisasi permission ke admin
        $admin->syncPermissions($permissions);

        // Cari user berdasarkan email kamu (Sandy)
        // Pastikan email ini sesuai dengan yang ada di database atau profil Google kamu
        $firstAdmin = User::where('email', 'sandy.mulia.k@gmail.com')->first(); 
        
        if ($firstAdmin) {
            $firstAdmin->assignRole($admin);
        }
    }
}