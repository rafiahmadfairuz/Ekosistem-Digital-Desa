<x-layouts.app title="Rincian sesi bantuan">
    <main id="main" class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-8">
        <p><a href="{{ route('bumdes.assistance.index') }}" class="min-h-11 inline-flex items-center text-sm font-medium text-brand underline underline-offset-4">Kembali ke permintaan bantuan</a></p>
        <x-ui.page-heading :title="$serviceLabels[$session->service_type] ?? 'Bantuan layanan digital'" :description="'Referensi sesi '.$session->session_reference" />

        <section class="overflow-hidden rounded-card border border-line bg-paper" aria-labelledby="session-info-heading">
            <header class="flex flex-col gap-3 border-b border-line bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="session-info-heading" class="text-lg font-semibold">Ringkasan sesi</h2>
                    <p class="mt-1 text-sm text-muted">Dibuat {{ $session->requested_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</p>
                </div>
                <div class="flex flex-col items-start gap-1 sm:items-end">
                    <x-ui.status-badge :status="$statusLabels[$session->status] ?? $session->status" />
                    @if ($session->completed_at)
                        <span class="text-xs text-muted">Selesai {{ $session->completed_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
                    @endif
                </div>
            </header>
            <dl class="grid gap-x-8 gap-y-5 p-5 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Petugas BUMDes</dt><dd class="mt-1 font-medium">{{ $session->operator?->username ?? 'Menunggu petugas' }}</dd></div>
                @unless ($isResident)
                    <div><dt class="text-muted">Warga</dt><dd class="mt-1 font-medium">{{ $session->resident?->user?->username ?? 'Akun warga' }}</dd></div>
                @endunless
                <div><dt class="text-muted">Permintaan diterima</dt><dd class="mt-1 font-medium">{{ $session->accepted_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? 'Menunggu petugas' }}</dd></div>
                <div><dt class="text-muted">Pendampingan dimulai</dt><dd class="mt-1 font-medium">{{ $session->started_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? 'Belum dimulai' }}</dd></div>
                <div><dt class="text-muted">Identitas diperiksa</dt><dd class="mt-1 font-medium">{{ $session->identity_verified_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? 'Belum dilakukan' }}</dd></div>
                <div><dt class="text-muted">Konfirmasi warga</dt><dd class="mt-1 font-medium">{{ $session->resident_confirmed_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? 'Belum dikonfirmasi' }}</dd></div>
            </dl>
            @if ($session->notes)
                <div class="border-t border-line bg-canvas/50 px-5 py-4"><h3 class="font-semibold">Catatan operasional</h3><p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-muted">{{ $session->notes }}</p></div>
            @endif
            @if ($session->rejection_reason)
                <div class="border-t border-line bg-red-50 px-5 py-4"><h3 class="font-semibold text-danger">Alasan permintaan ditolak</h3><p class="mt-2 break-words text-sm leading-6 text-muted">{{ $session->rejection_reason }}</p></div>
            @endif
            @if ($session->cancellation_reason)
                <div class="border-t border-line bg-canvas/50 px-5 py-4"><h3 class="font-semibold">Alasan pembatalan</h3><p class="mt-2 break-words text-sm leading-6 text-muted">{{ $session->cancellation_reason }}</p></div>
            @endif
        </section>

        @if ($canViewConversation)
            <section id="conversation" class="overflow-hidden rounded-card border border-line bg-paper" aria-labelledby="conversation-heading">
                <header class="flex flex-col gap-3 border-b border-line bg-paper px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 id="conversation-heading" class="text-lg font-semibold">Percakapan bantuan</h2>
                        <p class="mt-1 max-w-2xl text-sm text-muted">Ruang pesan pribadi antara warga dan petugas yang menangani sesi ini.</p>
                    </div>
                    <span class="inline-flex min-h-8 w-fit items-center rounded-full border border-line px-3 text-xs font-semibold {{ $canSendMessage ? 'border-brand/30 bg-emerald-50 text-brand-strong' : 'bg-canvas text-muted' }}">{{ $canSendMessage ? 'Percakapan aktif' : 'Riwayat percakapan' }}</span>
                </header>

                <ol class="max-h-[34rem] min-h-64 space-y-5 overflow-y-auto overscroll-contain bg-canvas/60 p-4 sm:p-6" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Riwayat percakapan bantuan">
                    @forelse ($messages as $message)
                        @php($isOwnMessage = (int) $message->sender_id === (int) auth()->id())
                        <li class="flex {{ $isOwnMessage ? 'justify-end' : 'justify-start' }}">
                            <article class="max-w-[min(88%,42rem)] min-w-0 rounded-2xl border {{ $isOwnMessage ? 'rounded-br-md border-brand/30 bg-emerald-50' : 'rounded-bl-md border-line bg-white' }} px-4 py-3 shadow-sm">
                                <header class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 text-xs">
                                    <p class="font-semibold text-ink">{{ $isOwnMessage ? 'Anda' : ($isResident ? 'Petugas BUMDes · '.$message->sender->username : 'Warga · '.$message->sender->username) }}</p>
                                    <time class="text-muted tabular-nums" datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</time>
                                </header>
                                <p class="mt-2 whitespace-pre-wrap break-words text-sm leading-6">{{ $message->body }}</p>
                            </article>
                        </li>
                    @empty
                        <li class="flex min-h-56 flex-col items-center justify-center rounded-card border border-dashed border-line bg-white px-5 py-8 text-center">
                            <span class="grid size-12 place-items-center rounded-full bg-emerald-50 text-brand-strong" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="1.8"><path d="M7 18.5 3.5 20l1.2-3.7A8 8 0 1 1 7 18.5Z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 10h8M8 13.5h5" stroke-linecap="round"/></svg></span>
                            <p class="mt-4 font-semibold">Percakapan dimulai di sini</p>
                            <p class="mt-1 max-w-md text-sm leading-6 text-muted">Gunakan ruang ini untuk membahas bantuan. Sampaikan hanya informasi yang diperlukan; jangan kirim kata sandi, OTP, PIN, atau data pembayaran.</p>
                        </li>
                    @endforelse
                </ol>

                @if ($messages?->hasPages())
                    <div class="border-t border-line px-5 py-3">{{ $messages->links() }}</div>
                @endif

                @if ($canSendMessage)
                    <form method="POST" action="{{ route('bumdes.assistance.messages.store', $session) }}#conversation" class="border-t border-line bg-paper p-4 sm:p-5">
                        @csrf
                        <div class="flex items-center justify-between gap-3"><label for="message-body" class="block text-sm font-semibold">Tulis pesan</label><span class="text-xs text-muted">Maksimal 4.000 karakter</span></div>
                        <textarea id="message-body" name="body" rows="3" maxlength="4000" required aria-describedby="message-help @error('body') message-error @enderror" aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}" class="mt-2 min-h-24 w-full rounded-control border {{ $errors->has('body') ? 'border-danger' : 'border-line' }} bg-paper px-3 py-2 text-base text-ink focus-visible:outline-2 focus-visible:outline-offset-2">{{ old('body') }}</textarea>
                        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p id="message-help" class="text-sm text-muted">Hindari informasi rahasia akun dan pembayaran.</p>
                            <x-ui.button type="submit">Kirim pesan</x-ui.button>
                        </div>
                        @error('body')<p id="message-error" class="mt-2 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </form>
                @else
                    <p class="border-t border-line px-5 py-4 text-sm text-muted">Percakapan ditutup karena sesi bantuan sudah berakhir. Riwayat pesan tetap dapat dibaca.</p>
                @endif
            </section>
        @elseif ($isResident && $session->status === 'pending')
            <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="conversation-pending-heading">
                <h2 id="conversation-pending-heading" class="font-semibold">Percakapan dibuka setelah permintaan diterima</h2>
                <p class="mt-2 text-sm text-muted">Petugas BUMDes akan meninjau permintaan Anda. Setelah salah satu petugas menerimanya, Anda dapat bertukar pesan di halaman ini.</p>
            </section>
        @endif

        @if ($letter)
            <section class="space-y-3 rounded-card border border-line bg-paper p-5" aria-labelledby="letter-receipt-heading">
                <h2 id="letter-receipt-heading" class="text-lg font-semibold">Rujukan permohonan surat</h2>
                <p class="text-sm text-muted">Warga tetap menjadi pemilik permohonan dan harus mengonfirmasi isinya di halaman surat sebelum permohonan dikirim ke RT.</p>
                <a class="inline-flex min-h-11 items-center text-brand underline underline-offset-4" href="{{ route('correspondence.show', $letter) }}">Buka permohonan {{ $letter->request_number }}</a>
            </section>
        @endif

        @if ($canAccept)
            <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="accept-heading">
                <h2 id="accept-heading" class="font-semibold">Terima permintaan bantuan</h2>
                <p class="mt-2 text-sm text-muted">Penerimaan menugaskan sesi ini kepada akun petugas Anda. Verifikasi identitas warga sesuai prosedur desa sebelum memulai pendampingan.</p>
                <form method="POST" action="{{ route('bumdes.assistance.accept', $session) }}" class="mt-4 space-y-4">
                    @csrf
                    <label for="identity_verified" class="flex min-h-11 items-start gap-3 rounded-control border border-line p-3">
                        <input id="identity_verified" name="identity_verified" type="checkbox" value="1" required class="mt-1 size-5 rounded border-line text-brand focus-visible:outline-2 focus-visible:outline-offset-2">
                        <span class="text-sm">Saya sudah bertemu warga dan memverifikasi identitasnya sesuai prosedur desa.</span>
                    </label>
                    @error('identity_verified')<p class="text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    <x-ui.button type="submit">Terima dan mulai membantu</x-ui.button>
                </form>
            </section>

            @if ($canReject)
                <details class="rounded-card border border-line bg-paper p-5">
                    <summary class="min-h-11 cursor-pointer font-semibold focus-visible:outline-2 focus-visible:outline-offset-2">Tolak permintaan</summary>
                    <form method="POST" action="{{ route('bumdes.assistance.reject', $session) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="reject_reason" class="block text-sm font-medium">Alasan yang akan dilihat warga</label>
                        <textarea id="reject_reason" name="reason" rows="3" required minlength="5" maxlength="500" class="w-full rounded-control border border-line bg-paper px-3 py-2 text-base"></textarea>
                        <x-ui.button type="submit" variant="danger">Tolak permintaan</x-ui.button>
                    </form>
                </details>
            @endif
        @endif

        @if (! $isResident && $session->status === 'in_progress' && $session->service_type === 'correspondence')
            <section class="rounded-card border border-line bg-paper p-5">
                <h2 class="font-semibold">Bantu menyiapkan permohonan surat</h2>
                <p class="mt-2 text-sm text-muted">Warga tetap pemilik permohonan dan akan meninjau serta mengonfirmasi draf dari akunnya.</p>
                <a href="{{ route('correspondence.create', ['assistance_session_id' => $session->id]) }}" class="mt-3 inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2">Siapkan draf surat</a>
            </section>
        @endif

        @if (! $isResident && $referenceTypes !== [] && $session->reference_type === null && $session->status === 'in_progress')
            <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="reference-heading">
                <h2 id="reference-heading" class="font-semibold">Tautkan catatan layanan yang sudah ada</h2>
                <p class="mt-2 text-sm text-muted">Pilih catatan layanan warga yang sudah tercatat. Sistem hanya menautkan catatan ini; data layanan dan statusnya tidak diubah.</p>
                @php($referenceOptions = collect($referenceRecords)->flatMap(fn ($records, $type) => collect($records)->map(fn ($record) => ['value' => $type.':'.$record['id'], 'label' => $record['label']]))->values())
                @if ($referenceOptions->isNotEmpty())
                    <form method="POST" action="{{ route('bumdes.assistance.reference.update', $session) }}" class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end">
                        @csrf @method('PUT')
                        <div class="min-w-0 flex-1">
                            <label for="reference" class="block text-sm font-medium">Catatan milik warga</label>
                            <select id="reference" name="reference" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base text-ink" aria-invalid="{{ $errors->has('reference') ? 'true' : 'false' }}">
                                <option value="">Pilih catatan layanan</option>
                                @foreach ($referenceOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected(old('reference') === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @error('reference')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <x-ui.button type="submit" variant="secondary">Tautkan catatan</x-ui.button>
                    </form>
                @else
                    <p class="mt-4 rounded-control border border-line bg-canvas p-4 text-sm" role="status">Belum ada catatan layanan yang cocok untuk ditautkan. Catatan akan muncul di sini setelah warga mengajukannya.</p>
                @endif
            </section>
        @elseif ($session->reference_type && ! $letter)
            <section class="rounded-card border border-line bg-paper p-5"><h2 class="font-semibold">Catatan layanan terkait</h2><p class="mt-2 text-sm text-muted">{{ $referenceTypes[$session->reference_type] ?? 'Catatan layanan' }} · ID {{ $session->reference_id }}</p></section>
        @endif

        @if ($canConfirm && $session->reference_type !== 'letter_request')
            <section class="rounded-card border border-brand/30 bg-emerald-50 p-5" aria-labelledby="resident-confirm-heading">
                <h2 id="resident-confirm-heading" class="font-semibold">Tinjau dan konfirmasi bantuan</h2>
                <p class="mt-2 text-sm">Dengan konfirmasi ini, Anda menyatakan sesi bantuan telah dilakukan. Konfirmasi tidak menyetujui permohonan layanan, pembayaran, pengiriman, atau keputusan administrasi apa pun.</p>
                <form method="POST" action="{{ route('bumdes.assistance.confirm', $session) }}" class="mt-4">@csrf <x-ui.button type="submit">Konfirmasi sesi bantuan</x-ui.button></form>
            </section>
        @endif

        @if ($canRequestConfirmation)
            <section class="rounded-card border border-line bg-paper p-5">
                <h2 class="font-semibold">Minta konfirmasi warga</h2>
                <p class="mt-1 text-sm text-muted">Warga harus masuk ke akunnya sendiri untuk meninjau dan mengonfirmasi. Tindakan ini tidak menyetujui atau mengirim permohonan layanan.</p>
                <form method="POST" action="{{ route('bumdes.assistance.request-confirmation', $session) }}" class="mt-4">@csrf <x-ui.button type="submit">Kirim untuk ditinjau warga</x-ui.button></form>
            </section>
        @endif

        @if ($canCancel)
            <details class="rounded-card border border-line bg-paper p-5">
                <summary class="min-h-11 cursor-pointer font-semibold focus-visible:outline-2 focus-visible:outline-offset-2">{{ $isResident ? 'Batalkan permintaan bantuan' : 'Batalkan sesi bantuan' }}</summary>
                <form method="POST" action="{{ route('bumdes.assistance.cancel', $session) }}" class="mt-4 space-y-3">
                    @csrf
                    <label for="reason" class="block text-sm font-medium">Alasan pembatalan</label>
                    <textarea id="reason" name="reason" rows="3" required minlength="5" maxlength="500" class="w-full rounded-control border border-line bg-paper px-3 py-2 text-base"></textarea>
                    <x-ui.button type="submit" variant="danger">{{ $isResident ? 'Batalkan permintaan' : 'Batalkan sesi' }}</x-ui.button>
                </form>
            </details>
        @endif

        <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="history-heading">
            <h2 id="history-heading" class="text-lg font-semibold">Riwayat sesi</h2>
            <ol class="mt-4 space-y-3">
                @forelse ($session->logs as $log)
                    <li class="border-l-2 border-brand/40 pl-4 text-sm">
                        <p class="font-medium">{{ str($log->action)->replace('_', ' ')->ucfirst() }}</p>
                        <p class="mt-1 text-muted">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }} · {{ $log->actor?->username ?? 'Petugas' }}</p>
                    </li>
                @empty
                    <li class="text-sm text-muted">Belum ada catatan aktivitas.</li>
                @endforelse
            </ol>
        </section>
    </main>
</x-layouts.app>
