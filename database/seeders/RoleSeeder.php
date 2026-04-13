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
        // 1. Setup Role & Permission (Hmph, ini sudah benar)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $permissions = [
            'approve devices',
            'manage users',
            'view logs'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin->syncPermissions($permissions);

        // 2. Tentukan Email Target (Gunakan yang ada di database kamu!)
        $email = 'sandymuliakesuma@gmail.com'; 

        // 3. Cari user, kalau tidak ada kita buatkan sekalian
        $sandy = User::where('email', $email)->first();
        
        if ($sandy) {
            $sandy->assignRole($admin);
            $this->command->info("Hmph, Role admin sudah diberikan ke: $email");
        } else {
            // Kalau usernya belum ada di DB, kita buatkan biar kamu bisa langsung login Google
            $newUser = User::create([
                'name' => 'Sandy Mulia Kesuma',
                'email' => $email,
                'password' => bcrypt('password123'), // Ini formalitas saja karena kamu pakai Google
                'is_active' => 1,
            ]);
            $newUser->assignRole($admin);
            $this->command->warn("User baru dibuat dan dijadikan admin: $email");
        }
    }
}