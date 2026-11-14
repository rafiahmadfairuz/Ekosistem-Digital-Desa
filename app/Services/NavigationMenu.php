<?php

namespace App\Services;

use App\Models\BumdesStaff;
use App\Models\InternetPackage;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;

class NavigationMenu
{
    /**
     * Build the application menu from the signed-in user's available abilities.
     *
     * @return array<string, array<int, array{label: string, route: string}>>
     */
    public function for(User $user): array
    {
        $user->loadMissing('roles');
        $roleNames = $user->roles->pluck('name');
        $hasRole = static fn (string $role): bool => $roleNames->contains($role);

        $groups = [
            'Beranda' => [
                ['label' => 'Ringkasan', 'route' => 'dashboard'],
            ],
        ];

        if ($hasRole('Resident')) {
            $hasResidentProfile = $user->resident()->exists();
            $seller = $user->seller;
            $groups['Wilayah'][] = [
                'label' => $hasResidentProfile ? 'Profil warga' : 'Lengkapi profil warga',
                'route' => 'profile.show',
            ];
            $groups['Pasar desa · Pembelian'] = [
                ['label' => 'Jelajahi produk', 'route' => 'marketplace.index'],
                ['label' => 'Keranjang', 'route' => 'marketplace.cart'],
                ['label' => 'Pesanan saya', 'route' => 'marketplace.orders.index'],
            ];
            if ($seller !== null && $seller->verification_status !== 'approved') {
                $label = in_array($seller->verification_status, ['rejected', 'inactive'], true)
                    ? 'Perbaiki pengajuan penjual'
                    : ($seller->verification_status === 'suspended' ? 'Status penjual' : 'Status pengajuan penjual');
                $groups['Pasar desa · Penjual'][] = ['label' => $label, 'route' => 'marketplace.seller.application'];
            } elseif ($seller === null && $hasResidentProfile) {
                $groups['Pasar desa · Penjual'][] = ['label' => 'Daftar sebagai penjual', 'route' => 'marketplace.seller.application'];
            }
        }

        if ($hasRole('Seller') && $user->seller()->exists()) {
            if ($user->seller->verification_status === 'approved') {
                $groups['Pasar desa · Penjual'][] = ['label' => 'Produk saya', 'route' => 'marketplace.seller.products'];
                $groups['Pasar desa · Penjual'][] = ['label' => 'Saldo dan pendapatan', 'route' => 'marketplace.seller.payout-account.edit'];
            }
            $groups['Pasar desa · Penjual'][] = ['label' => 'Pesanan toko', 'route' => 'marketplace.seller.orders'];
        }

        if ($hasRole('BUMDes Manager') && $user->bumdesStaff()->where('employment_status', 'active')->exists()) {
            $groups['Pengelolaan pasar desa'] = [
                ['label' => 'Pengajuan penjual', 'route' => 'marketplace.management.sellers'],
                ['label' => 'Kategori produk', 'route' => 'marketplace.management.categories'],
                ['label' => 'Moderasi produk', 'route' => 'marketplace.management.products'],
            ];
        }

        if ($hasRole('Village Admin')) {
            $groups['Wilayah'] = array_merge($groups['Wilayah'] ?? [], [
                ['label' => 'Verifikasi wilayah warga', 'route' => 'admin.resident-region-verification.index'],
                ['label' => 'Pengaturan desa', 'route' => 'admin.village-settings.index'],
            ]);
        }

        if (($hasRole('RT') || $hasRole('RW')) && $user->can('viewAny', Resident::class)) {
            $groups['Wilayah'][] = ['label' => 'Warga wilayah tugas', 'route' => 'officer.residents.index'];
        }

        if ($hasRole('Super Admin')) {
            $groups['Akun dan akses'] = [
                ['label' => 'Aktivasi akun', 'route' => 'admin.users.index'],
                ['label' => 'Akun dan peran', 'route' => 'admin.users.manage'],
                ['label' => 'Pembayaran Xendit marketplace', 'route' => 'admin.marketplace.xendit-seller-accounts.index'],
            ];
            $groups['Wilayah'] = array_merge($groups['Wilayah'] ?? [], [
                ['label' => 'Data desa, RW, dan RT', 'route' => 'admin.regions.index'],
                ['label' => 'Pengaturan desa', 'route' => 'admin.village-settings.index'],
                ['label' => 'Penugasan RT/RW', 'route' => 'admin.region-assignments.index'],
            ]);
            $groups['Pemantauan'] = [
                ['label' => 'Log audit', 'route' => 'admin.audit-logs.index'],
            ];
        }

        if ($user->can('viewAny', BumdesStaff::class)) {
            $groups['Layanan BUMDes'] = [
                ['label' => 'Data staf', 'route' => 'bumdes.staff.index'],
            ];

            if ($hasRole('BUMDes Manager') && $user->bumdesStaff()->where('employment_status', 'active')->exists()) {
                $groups['Layanan BUMDes'][] = ['label' => 'Laporan usaha', 'route' => 'bumdes.reports.index'];
                $groups['Layanan BUMDes'][] = ['label' => 'Laporan operasional', 'route' => 'bumdes.assistance.reports'];
            }
        }

        if ($hasRole('Resident') && $user->resident !== null) {
            $groups['Layanan BUMDes'][] = ['label' => 'Riwayat bantuan BUMDes', 'route' => 'bumdes.assistance.index'];
        }

        if (($hasRole('BUMDes Manager') || $hasRole('BUMDes Operator')) && $user->bumdesStaff()->where('employment_status', 'active')->exists()) {
            $groups['Layanan BUMDes'][] = ['label' => 'Sesi bantuan warga', 'route' => 'bumdes.assistance.index'];
        }

        if ($user->can('viewAny', LetterRequest::class)) {
            $groups['Persuratan'] = [
                ['label' => 'Permohonan surat', 'route' => 'correspondence.index'],
            ];
            if ($user->can('manageTypes', LetterRequest::class)) {
                $groups['Persuratan'][] = ['label' => 'Jenis surat', 'route' => 'correspondence.types'];
            }
        }

        if ($hasRole('Resident') || (($hasRole('BUMDes Manager') || $hasRole('BUMDes Operator')) && $user->bumdesStaff()->where('employment_status', 'active')->exists())) {
            $groups['Layanan BUMDes'][] = ['label' => 'Internet & Wi‑Fi', 'route' => 'services.index'];
        }

        if ($user->can('manage', InternetPackage::class)) {
            $groups['Layanan BUMDes'][] = ['label' => 'Pengaturan Internet & Wi‑Fi', 'route' => 'services.management'];
        }

        $seenRoutes = [];
        foreach ($groups as $groupName => $items) {
            $groups[$groupName] = array_values(array_filter($items, function (array $item) use (&$seenRoutes): bool {
                if (isset($seenRoutes[$item['route']])) {
                    return false;
                }

                $seenRoutes[$item['route']] = true;

                return true;
            }));
        }

        return array_filter($groups);
    }
}
