
<x-layouts.app title="Peran dan akun">
    <style>
        .ur-shell {
            max-width: 76rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .ur-shell { padding: 2.25rem 2rem 4rem; }
        }
        .ur-header {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .ur-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                gap: 2rem;
            }
        }
        .ur-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .ur-kicker-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #10b981;
        }
        .ur-title {
            margin: 0.375rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: #1e293b;
        }
        @media (min-width: 640px) {
            .ur-title { font-size: 1.625rem; }
        }
        .ur-desc {
            margin: 0.5rem 0 0;
            max-width: 44rem;
            font-size: 0.875rem;
            line-height: 1.65;
            color: #64748b;
        }
        .ur-policy-note {
            margin-top: 1.25rem;
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            padding: 0.875rem 1.125rem;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748b;
            box-shadow: 0 1px 4px -1px rgba(15, 23, 42, 0.03);
        }
        .ur-replacement-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.625rem;
            padding: 0 1.125rem;
            border-radius: 0.75rem;
            border: 1px solid #a7f3d0;
            background-color: #ecfdf5;
            color: #065f46;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease;
        }
        .ur-replacement-btn:hover {
            background-color: #059669;
            border-color: #059669;
            color: #ffffff;
        }
        .ur-alert-success {
            margin-top: 1.25rem;
            border-radius: 0.875rem;
            border: 1px solid #a7f3d0;
            background-color: #ecfdf5;
            padding: 0.875rem 1.125rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #065f46;
        }
        .ur-alert-error {
            margin-top: 1rem;
            border-radius: 0.875rem;
            border: 1px solid #fecaca;
            background-color: #fef2f2;
            padding: 0.875rem 1.125rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #991b1b;
        }
        .ur-list {
            margin-top: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.125rem;
        }
        .ur-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }
        .ur-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px -6px rgba(15, 23, 42, 0.06);
        }
        .ur-card-body {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 1.375rem 1.5rem;
        }
        @media (min-width: 1024px) {
            .ur-card-body {
                grid-template-columns: minmax(13rem, 0.95fr) minmax(19rem, 1.25fr) minmax(15rem, 0.9fr);
                align-items: start;
                gap: 1.75rem;
            }
        }
        .ur-col-divider {
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }
        @media (min-width: 1024px) {
            .ur-col-divider {
                padding-top: 0;
                padding-left: 1.5rem;
                border-top: none;
                border-left: 1px solid #f1f5f9;
            }
        }
        .ur-user-head {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .ur-avatar {
            width: 2.375rem;
            height: 2.375rem;
            border-radius: 0.6875rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ur-username {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1e293b;
            word-break: break-all;
            line-height: 1.3;
        }
        .ur-email {
            margin: 0.2rem 0 0;
            font-size: 0.75rem;
            color: #64748b;
            word-break: break-all;
        }
        .ur-badges {
            margin-top: 0.875rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
        }
        .ur-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 9999px;
            padding: 0.2rem 0.625rem;
            font-size: 0.6875rem;
            font-weight: 600;
        }
        .ur-badge-neutral {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .ur-badge-emerald {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #047857;
        }
        .ur-badge-amber {
            background-color: #fefce8;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .ur-badge-red {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .ur-section-label {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }
        .ur-protected-box {
            border-radius: 0.75rem;
            border: 1px solid #d1fae5;
            background-color: #f0fdf4;
            padding: 0.875rem 1rem;
        }
        .ur-protected-title {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #065f46;
        }
        .ur-protected-text {
            margin: 0.35rem 0 0;
            font-size: 0.75rem;
            line-height: 1.55;
            color: #475569;
        }
        .ur-roles-grid {
            margin-top: 0.625rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.45rem;
        }
        @media (min-width: 640px) {
            .ur-roles-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .ur-role-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.375rem;
            padding: 0.375rem 0.625rem;
            border-radius: 0.625rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.75rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: border-color 0.14s ease, background-color 0.14s ease;
        }
        .ur-role-option:hover {
            border-color: #a7f3d0;
            background-color: #ecfdf5;
        }
        .ur-role-option input[type="checkbox"] { accent-color: #059669; }
        .ur-role-option input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        .ur-helper {
            margin: 0.5rem 0 0;
            font-size: 0.75rem;
            line-height: 1.55;
            color: #94a3b8;
        }
        .ur-btn-primary {
            margin-top: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.375rem;
            padding: 0 1rem;
            border-radius: 0.625rem;
            border: none;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.16s ease, box-shadow 0.16s ease;
        }
        .ur-btn-primary:hover {
            background-color: #047857;
            box-shadow: 0 4px 12px -3px rgba(5, 150, 105, 0.3);
        }
        .ur-select-row {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ur-select {
            min-height: 2.375rem;
            width: 100%;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0 0.625rem;
            font-size: 0.8125rem;
            color: #334155;
            transition: border-color 0.15s ease;
        }
        .ur-select:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        }
        .ur-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.375rem;
            padding: 0 0.875rem;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
        }
        .ur-btn-secondary:hover {
            border-color: #6ee7b7;
            background-color: #ecfdf5;
            color: #047857;
        }
        .ur-pending-link {
            margin-top: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .ur-pending-link:hover { color: #047857; }
        .ur-card-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            padding: 0.75rem 1.5rem;
        }
        .ur-foot-note {
            margin: 0;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .ur-btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.125rem;
            padding: 0 0.875rem;
            border-radius: 0.5625rem;
            border: 1px solid #fecaca;
            background-color: #ffffff;
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .ur-btn-danger:hover {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }
        .ur-pagination { margin-top: 1.5rem; }
    </style>

    <main id="main" class="ur-shell">
        <div class="ur-header">
            <div>
                <p class="ur-kicker">
                    <span class="ur-kicker-dot" aria-hidden="true"></span>
                    <span>Akses teknis · Super Admin</span>
                </p>

                <h1 class="ur-title">Akun dan peran</h1>

                <p class="ur-desc">
                    Tetapkan hanya peran yang telah disetujui. Peran Kepala Desa hanya dapat diberikan kepada akun aktif dengan email terverifikasi, dan hanya satu akun dapat memegangnya. Peran RT/RW belum memberi akses wilayah tanpa penugasan aktif.
                </p>
            </div>

            <a href="{{ route('admin.users.village-head.replacement') }}"
               class="ur-replacement-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                <span>Buka pergantian Kepala Desa</span>
                <span aria-hidden="true">›</span>
            </a>
        </div>

        <p class="ur-policy-note">
            Profil warga yang belum dipakai dalam permohonan surat akan ikut dihapus bersama akun. Riwayat penugasan wilayah tetap disimpan dengan referensi akun yang sudah dihapus. Akun yang terkait riwayat surat atau data staf BUMDes harus dinonaktifkan agar data layanan tetap terjaga.
        </p>

        @if(session('status'))
            <p class="ur-alert-success" role="status">{{ session('status') }}</p>
        @endif

        @error('role_ids')
            <p class="ur-alert-error" role="alert">{{ $message }}</p>
        @enderror

        @error('account_delete')
            <p class="ur-alert-error" role="alert">{{ $message }}</p>
        @enderror

        @error('account_status')
            <p class="ur-alert-error" role="alert">{{ $message }}</p>
        @enderror

        <div class="ur-list">
            @foreach ($users as $user)
                <article class="ur-card">
                    <div class="ur-card-body">
                        {{-- Informasi akun --}}
                        <div class="min-w-0" data-unsaved-form data-confirm="Simpan perubahan peran untuk akun ini?">
                            <div class="ur-user-head">
                                <span class="ur-avatar" aria-hidden="true">
                                    {{ mb_substr($user->username ?? '?', 0, 1) }}
                                </span>

                                <div class="min-w-0">
                                    <h2 class="ur-username">{{ $user->username }}</h2>
                                    <p class="ur-email">{{ $user->email ?? 'Email tidak tersedia' }}</p>
                                </div>
                            </div>

                            <div class="ur-badges">
                                <span class="ur-badge {{ match($user->account_status) {
                                    'active' => 'ur-badge-emerald',
                                    'pending' => 'ur-badge-amber',
                                    'suspended', 'disabled' => 'ur-badge-red',
                                    default => 'ur-badge-neutral'
                                } }}">
                                    Status: {{ match($user->account_status) {
                                        'active' => 'Aktif',
                                        'suspended' => 'Ditangguhkan',
                                        'disabled' => 'Dinonaktifkan',
                                        'pending' => 'Menunggu aktivasi',
                                        default => 'Tidak diketahui'
                                    } }}
                                </span>

                                @if($user->hasVerifiedEmail())
                                    <span class="ur-badge ur-badge-emerald">Email terverifikasi</span>
                                @else
                                    <span class="ur-badge ur-badge-red">Email belum terverifikasi</span>
                                @endif
                            </div>
                        </div>

                        {{-- Pengaturan peran --}}
                        <div class="ur-col-divider min-w-0">
                            @if($user->roles->contains(fn ($role) => in_array($role->name, ['Super Admin', 'Village Head'], true)))
                                <div class="ur-protected-box">
                                    <p class="ur-protected-title">Peran terlindungi</p>
                                    <p class="ur-protected-text">
                                        {{ $user->roles->pluck('name')->intersect(['Super Admin', 'Village Head'])->implode(', ') }}
                                        · perubahan peran dilakukan melalui proses pergantian khusus.
                                    </p>
                                </div>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.users.roles.update', $user) }}"
                                      class="min-w-0"
                                      style="margin: 0;"
                                      data-role-form>
                                    @csrf
                                    @method('PUT')

                                    <fieldset style="border: 0; padding: 0; margin: 0;">
                                        <legend class="ur-section-label">Peran</legend>

                                        <div class="ur-roles-grid">
                                            @foreach($roles as $role)
                                                <label class="ur-role-option">
                                                    <input
                                                        class="size-4 shrink-0"
                                                        type="checkbox"
                                                        name="role_ids[]"
                                                        value="{{ $role->id }}"
                                                        data-role-name="{{ $role->name }}"
                                                        @checked($user->roles->contains('id', $role->id))>

                                                    <span class="break-words">{{ $role->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>

                                    <p class="ur-helper">
                                        Kombinasi multi-peran hanya diizinkan untuk RT + Resident atau RW + Resident. Village Head harus menjadi satu-satunya peran.
                                    </p>

                                    <button class="ur-btn-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                        Simpan peran
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Status akun --}}
                        <div class="ur-col-divider min-w-0">
                            @if($user->account_status === 'pending')
                                <div>
                                    <p class="ur-section-label">Status akun</p>

                                    <p class="ur-helper" style="margin-top: 0.375rem; color: #64748b;">
                                        Akun menunggu aktivasi; perubahan status tersedia setelah email diverifikasi.
                                    </p>

                                    <a class="ur-pending-link" href="{{ route('admin.users.index') }}">
                                        Buka aktivasi akun
                                    </a>
                                </div>
                            @else
                                <form method="POST"
                                      action="{{ route('admin.users.status.update', $user) }}"
                                      data-unsaved-form
                                      data-confirm="Perbarui status akun ini?"
                                      style="margin: 0;">
                                    @csrf
                                    @method('PUT')

                                    <div class="min-w-0">
                                        <label class="ur-section-label" for="status-{{ $user->id }}">
                                            Status akun
                                        </label>

                                        <div class="ur-select-row">
                                            <select id="status-{{ $user->id }}" name="account_status" class="ur-select">
                                                <option value="active" @selected($user->account_status === 'active')>Aktif</option>
                                                <option value="suspended" @selected($user->account_status === 'suspended')>Ditangguhkan sementara</option>
                                                <option value="disabled" @selected($user->account_status === 'disabled')>Dinonaktifkan</option>
                                            </select>

                                            <button class="ur-btn-secondary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                                Perbarui
                                            </button>
                                        </div>

                                        <p class="ur-helper">
                                            Ditangguhkan: login dihentikan sementara, penugasan RT/RW dipertahankan. Dinonaktifkan: sesi dicabut dan penugasan aktif diakhiri.
                                        </p>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Tindakan akun --}}
                    <div class="ur-card-foot">
                        @if((int) $user->id === (int) auth()->id())
                            <p class="ur-foot-note">Akun yang sedang digunakan tidak dapat dihapus.</p>
                        @elseif($user->roles->contains(fn ($role) => in_array($role->name, ['Super Admin', 'Village Head'], true)))
                            <p class="ur-foot-note">Akun dengan peran Super Admin atau Village Head dilindungi dari penghapusan.</p>
                        @else
                            <span class="ur-foot-note">Tindakan penghapusan akun bersifat permanen.</span>

                            <form method="POST"
                                  action="{{ route('admin.users.destroy', $user) }}"
                                  data-confirm="Hapus akun {{ $user->username }} secara permanen? Data yang sudah terhubung dengan profil atau riwayat layanan tidak dapat dihapus."
                                  style="margin: 0;">
                                @csrf
                                @method('DELETE')

                                <button class="ur-btn-danger focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                    Hapus akun
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="ur-pagination">
            {{ $users->links() }}
        </div>
    </main>

    {{-- Pengamanan pilihan peran di sisi tampilan --}}
    <script>
        document.querySelectorAll('[data-role-form]').forEach((form) => {
            const checkboxes = [...form.querySelectorAll('input[name="role_ids[]"]')];

            const villageHead = checkboxes.find(
                (checkbox) => checkbox.dataset.roleName === 'Village Head'
            );

            const updateRoleOptions = (changedCheckbox) => {
                if (villageHead?.checked) {
                    checkboxes
                        .filter((checkbox) => checkbox !== villageHead)
                        .forEach((checkbox) => {
                            checkbox.checked = false;
                            checkbox.disabled = true;
                        });

                    return;
                }

                if (changedCheckbox?.checked && changedCheckbox !== villageHead && villageHead) {
                    villageHead.checked = false;
                }

                const hasOtherRole = checkboxes.some(
                    (checkbox) => checkbox !== villageHead && checkbox.checked
                );

                if (villageHead) {
                    villageHead.disabled = hasOtherRole;
                }

                checkboxes
                    .filter((checkbox) => checkbox !== villageHead)
                    .forEach((checkbox) => {
                        checkbox.disabled = false;
                    });
            };

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    updateRoleOptions(checkbox);
                });
            });

            updateRoleOptions(null);
        });
    </script>
</x-layouts.app>
