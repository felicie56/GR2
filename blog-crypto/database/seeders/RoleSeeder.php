<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 3 role cơ bản
        $roles = ['USER', 'AUTHOR', 'ADMIN'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Tạo tài khoản admin mặc định (nếu chưa có)
        $adminEmail = 'admin@example.com';

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'), // sau có thể đổi lại
            ]
        );

        // Gán role ADMIN cho admin user
        $adminRole = Role::where('name', 'ADMIN')->first();

        if ($adminRole && ! $admin->roles()->where('roles.id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}
