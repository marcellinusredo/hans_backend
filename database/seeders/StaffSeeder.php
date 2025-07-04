<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('nama_role', 'Super Admin')->first();
        $ownerRole = Role::where('nama_role', 'Pemilik')->first();

        Staff::create([
            'nama_staff' => 'Super Admin',
            'nomor_telp_staff' => '08123456789',
            'alamat_staff' => 'Klaten',
            'username_staff' => 'superadmin',
            'password_staff' => Hash::make('superadmin'), // Ganti dengan password yang kuat
            'role_id' => $superAdminRole->id_role,
        ]);
    }
}
