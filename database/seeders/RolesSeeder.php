<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin' => 'Administrasi teknis platform.',
            'BUMDes Manager' => 'Pengelolaan operasional BUMDes.',
            'BUMDes Operator' => 'Pendampingan layanan digital BUMDes.',
            'Village Admin' => 'Pengelolaan administrasi desa.',
            'RT' => 'Petugas wilayah RT yang ditugaskan.',
            'RW' => 'Petugas wilayah RW yang ditugaskan.',
            'Resident' => 'Warga yang menggunakan layanan.',
            'Seller' => 'Penjual yang telah disetujui.',
            'Village Head' => 'Pejabat penandatangan kepala desa.',
        ];

        foreach ($roles as $name => $description) {
            Role::query()->updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
