<x-layouts.app :title="$letterRequest->request_number">
    @php
        $statusNames = ['draft' => 'Menunggu konfirmasi warga', 'rt_review' => 'Menunggu pemeriksaan RT', 'rw_review' => 'Menunggu pemeriksaan RW', 'village_review' => 'Menunggu administrasi desa', 'signature_pending' => 'Menunggu Kepala Desa', 'completed' => 'Selesai', 'revision_required' => 'Perlu perbaikan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
        $stages = ['submitted' => 'Dikirim warga', 'rt_review' => 'Pemeriksaan RT', 'rw_review' => 'Pemeriksaan RW', 'village_review' => 'Administrasi desa', 'signature_pending' => 'Kepala Desa', 'completed' => 'Terbit'];
        $stageOrder = array_keys($stages);
        $currentOrder = $letterRequest->status === 'draft' ? -1 : array_search($letterRequest->status, $stageOrder, true);
        $currentOrder = $currentOrder === false ? ($letterRequest->status === 'revision_required' ? 1 : -1) : $currentOrder;
    @endphp
    <main id="main" class="mx-auto max-w-5xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Persuratan', 'url' => route('correspondence.index')], ['label' => $letterRequest->request_number]]" />
        <x-ui.page-heading class="mt-5" :title="$letterRequest->letterType->name" description="Rincian permohonan dan tahapan proses.">
            <x-slot:actions><x-ui.status-badge :status="$statusNames[$letterRequest->status] ?? $letterRequest->status" :tone="match ($letterRequest->status) { 'completed' => 'success', 'rejected' => 'danger', 'revision_required', 'signature_pending' => 'warning', default => 'neutral' }" /></x-slot:actions>
        </x-ui.page-heading>

        <section class="mt-6 rounded-card border border-line bg-paper p-5" aria-labelledby="tracking-heading">
            <h2 id="tracking-heading" class="text-base font-semibold">Pelacakan permohonan</h2>
            <p class="mt-2 font-mono text-sm text-muted">{{ $letterRequest->request_number }} <span class="font-sans">· {{ $letterRequest->submission_channel === 'assisted_bumdes' ? 'Layanan berbantuan BUMDes' : 'Pengajuan mandiri' }}</span></p>
            <ol aria-label="Tahapan persetujuan" class="mt-5 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($stages as $stage => $label)
                    @php($stageIndex = array_search($stage, $stageOrder, true))
                    @php($isDone = $letterRequest->status === 'completed' || ($currentOrder >= 0 && $stageIndex < $currentOrder))
                    @php($isCurrent = $stage === $letterRequest->status || ($letterRequest->status === 'revision_required' && $stageIndex === 1))
                    <li class="min-h-16 rounded-control border {{ $isCurrent ? 'border-brand bg-success-bg text-success-ink' : ($isDone ? 'border-line bg-slate-100 text-ink' : 'border-line bg-paper text-muted') }} px-3 py-2">
                        <span class="block text-xs font-semibold">{{ $isDone ? 'Selesai' : ($isCurrent ? 'Saat ini' : 'Berikutnya') }}</span>
                        <span class="mt-1 block text-sm">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
            @if (in_array($letterRequest->status, ['rejected', 'revision_required'], true))
                <p class="mt-4 rounded-control border border-amber-300 bg-warning-bg p-3 text-sm leading-6 text-warning-ink"><strong>{{ $letterRequest->status === 'rejected' ? 'Alasan penolakan:' : 'Catatan perbaikan:' }}</strong> {{ $letterRequest->rejection_reason }}</p>
            @endif
        </section>

        <div class="mt-5 grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="details-heading">
                <h2 id="details-heading" class="text-base font-semibold">Informasi permohonan</h2>
                <dl class="mt-4 divide-y divide-slate-200">
                    @foreach ($fields as $label => $value)
                        <div class="grid gap-1 py-3 sm:grid-cols-[12rem_1fr] sm:gap-4"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="min-w-0 break-words text-sm">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if ($letterRequest->submission_channel === 'assisted_bumdes')
                    <p class="mt-3 border-t border-line pt-3 text-sm text-muted">Dibantu oleh akun petugas: {{ $letterRequest->creator->username }}. Warga pemilik permohonan tetap {{ $letterRequest->resident->user->username }}.</p>
                @endif
            </section>

            <section class="space-y-5">
                <div class="rounded-card border border-line bg-paper p-5">
                    <h2 class="text-base font-semibold">Riwayat keputusan</h2>
                    @forelse ($approvals as $approval)
                        <article class="border-b border-line py-3 last:border-0">
                            <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $approval->approval_level)) }} · {{ ucfirst(str_replace('_', ' ', $approval->decision)) }}</p>
                            <p class="mt-1 text-xs text-muted">{{ $approval->reviewer->username }} · <time datetime="{{ $approval->reviewed_at->toIso8601String() }}">{{ $approval->reviewed_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</time></p>
                            @if ($approval->comments)<p class="mt-2 break-words text-sm leading-6">{{ $approval->comments }}</p>@endif
                        </article>
                    @empty
                        <p class="mt-3 text-sm text-muted">Belum ada keputusan pemeriksaan.</p>
                    @endforelse
                </div>

                <div class="rounded-card border border-line bg-paper p-5">
                    <h2 class="text-base font-semibold">Lampiran</h2>
                    @forelse ($letterRequest->attachments as $attachment)
                        <a href="{{ route('correspondence.attachments.download', [$letterRequest, $attachment]) }}" class="mt-3 flex min-h-11 items-center justify-between gap-3 border-b border-line py-2 text-sm text-brand-strong underline underline-offset-4 last:border-0">
                            <span class="min-w-0 break-all">{{ $attachment->original_filename ?: 'Lampiran' }} · versi {{ $attachment->request_version }}</span><span class="shrink-0">Unduh</span>
                        </a>
                    @empty
                        <p class="mt-3 text-sm text-muted">Tidak ada lampiran.</p>
                    @endforelse
                </div>

                @if ($letterRequest->documents->isNotEmpty())
                    <div class="rounded-card border border-line bg-paper p-5">
                        <h2 class="text-base font-semibold">Dokumen versi</h2>
                        <ul class="mt-3 divide-y divide-slate-200">
                            @foreach ($letterRequest->documents as $document)
                                <li class="flex min-h-14 flex-wrap items-center justify-between gap-3 py-2 text-sm">
                                    <span>Versi {{ $document->version }}{{ $document->signed_at ? ' · Prototipe ditandatangani' : ' · Draf pemeriksaan' }}</span>
                                    @if (auth()->user()->can('downloadDocument', [$letterRequest, $document]))
                                        <a href="{{ route('correspondence.documents.download', [$letterRequest, $document]) }}" class="inline-flex min-h-11 items-center text-brand-strong underline underline-offset-4">Unduh PDF</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if ($letterRequest->status === 'completed')<p class="mt-3 text-xs leading-5 text-muted">Dokumen prototipe ini tidak menggunakan tanda tangan elektronik tersertifikasi (PSrE).</p>@endif
                    </div>
                @endif
            </section>
        </div>

        @if ($canConfirm)
            <section class="mt-5 rounded-card border border-brand/40 bg-success-bg p-5">
                <h2 class="text-base font-semibold text-success-ink">Periksa dan konfirmasi permohonan</h2>
                <p class="mt-2 text-sm leading-6 text-success-ink">Petugas BUMDes membantu mengisi formulir ini. Pastikan informasi dan lampiran benar sebelum meneruskan permohonan ke RT.</p>
                <form method="POST" action="{{ route('correspondence.confirm', $letterRequest) }}" data-confirm="Kirim permohonan ini ke pemeriksaan RT?" class="mt-3">
                    @csrf
                    <x-ui.button type="submit">Saya setuju dan kirim ke RT</x-ui.button>
                </form>
            </section>
        @endif

        @if ($canReview)
            <section class="mt-5 rounded-card border border-line bg-paper p-5">
                <h2 class="text-base font-semibold">Keputusan pemeriksaan</h2>
                <p class="mt-2 text-sm leading-6 text-muted">Setujui untuk meneruskan ke tahap berikutnya. Penolakan dan permintaan perbaikan wajib disertai alasan.</p>
                <form method="POST" action="{{ route('correspondence.decision', $letterRequest) }}" data-confirm="Catat keputusan pemeriksaan ini?" class="mt-4 space-y-3">
                    @csrf
                    <label for="decision" class="block text-sm font-medium">Keputusan</label>
                    <select id="decision" name="decision" required class="min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-ink focus-visible:outline-offset-2">
                        <option value="approved">Setujui dan teruskan</option>
                        <option value="revision_required">Minta perbaikan</option>
                        <option value="rejected">Tolak permohonan</option>
                    </select>
                    <label for="comments" class="block text-sm font-medium">Alasan atau catatan <span class="font-normal text-muted">(wajib untuk penolakan/perbaikan)</span></label>
                    <textarea id="comments" name="comments" rows="3" maxlength="2000" class="min-h-24 w-full rounded-control border border-slate-400 bg-paper px-3 py-2 text-ink focus-visible:outline-offset-2">{{ old('comments') }}</textarea>
                    @error('comments')<p class="text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    <x-ui.button type="submit" data-submit-button>Catat keputusan</x-ui.button>
                </form>
            </section>
        @endif

        @if ($canSign)
            <section class="mt-5 rounded-card border border-warning-ink/30 bg-warning-bg p-5">
                <h2 class="text-base font-semibold">Keputusan Kepala Desa</h2>
                <p class="mt-2 text-sm leading-6 text-warning-ink">Tindakan ini mencatat persetujuan prototipe dan membuat versi PDF final. Ini bukan tanda tangan elektronik tersertifikasi atau klaim keabsahan hukum.</p>
                <form method="POST" action="{{ route('correspondence.signature', $letterRequest) }}" data-confirm="Catat persetujuan tanda tangan prototipe dan terbitkan dokumen?" class="mt-4">
                    @csrf
                    <input type="hidden" name="decision" value="signed">
                    <label class="flex min-h-12 items-start gap-3 text-sm leading-6"><input type="checkbox" name="prototype_acknowledged" value="1" required class="mt-1 size-4 accent-emerald-700"><span>Saya mengonfirmasi persetujuan prototipe. Saya memahami dokumen ini tidak memakai tanda tangan PSrE.</span></label>
                    @error('prototype_acknowledged')<p class="text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    <x-ui.button type="submit" class="mt-3">Catat persetujuan prototipe</x-ui.button>
                </form>
                <form method="POST" action="{{ route('correspondence.signature', $letterRequest) }}" data-confirm="Tolak permohonan ini? Alasan wajib dicantumkan." class="mt-5 border-t border-warning-ink/20 pt-4">
                    @csrf
                    <input type="hidden" name="decision" value="rejected">
                    <label for="head-reason" class="block text-sm font-medium">Alasan penolakan</label>
                    <textarea id="head-reason" name="comments" required maxlength="2000" rows="3" class="mt-1 min-h-24 w-full rounded-control border border-slate-400 bg-paper px-3 py-2 text-ink focus-visible:outline-offset-2"></textarea>
                    <x-ui.button type="submit" variant="danger" class="mt-3">Tolak permohonan</x-ui.button>
                </form>
            </section>
        @endif

        @if ($canRevise)
            <section id="revise-form" class="mt-5 rounded-card border border-line bg-paper p-5">
                <h2 class="text-base font-semibold">Perbaiki permohonan</h2>
                <p class="mt-2 text-sm text-muted">Riwayat dan berkas sebelumnya tetap tersimpan sebagai versi terdahulu.</p>
                <form method="POST" action="{{ route('correspondence.revise', $letterRequest) }}" enctype="multipart/form-data" data-unsaved-form class="mt-4 space-y-4">
                    @csrf @method('PUT')
                @foreach ($letterRequest->effectiveLetterType()->required_fields ?? [] as $field)
                        <div>
                            <label for="revise-{{ $field['key'] }}" class="block text-sm font-medium">{{ $field['label'] }} @if ($field['required'] ?? true)<span aria-hidden="true">*</span>@endif</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="revise-{{ $field['key'] }}" name="fields[{{ $field['key'] }}]" rows="3" @required($field['required'] ?? true) class="mt-1 min-h-24 w-full rounded-control border border-slate-400 bg-paper px-3 py-2">{{ old('fields.'.$field['key'], $fields[$field['label']] ?? '') }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="revise-{{ $field['key'] }}" name="fields[{{ $field['key'] }}]" @required($field['required'] ?? true) class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3">
                                    <option value="">Pilih {{ strtolower($field['label']) }}</option>
                                    @foreach ($field['options'] ?? [] as $option)<option value="{{ $option }}" @selected(old('fields.'.$field['key'], $fields[$field['label']] ?? null) === $option)>{{ $option }}</option>@endforeach
                                </select>
                            @else
                                <input id="revise-{{ $field['key'] }}" type="{{ match ($field['type']) { 'date' => 'date', 'integer' => 'number', default => 'text' } }}" name="fields[{{ $field['key'] }}]" value="{{ old('fields.'.$field['key'], $fields[$field['label']] ?? '') }}" @required($field['required'] ?? true) class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3">
                            @endif
                            @error('fields.'.$field['key'])<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    @foreach ($letterRequest->effectiveLetterType()->required_attachments ?? [] as $attachment)
                        <div><label for="rev-attachment-{{ $attachment['key'] }}" class="block text-sm font-medium">Lampiran baru: {{ $attachment['label'] }}</label><input id="rev-attachment-{{ $attachment['key'] }}" type="file" name="attachments[{{ $attachment['key'] }}]" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 p-2">@error('attachments.'.$attachment['key'])<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror</div>
                    @endforeach
                    <x-ui.button type="submit" data-submit-button>Kirim perbaikan ke RT</x-ui.button>
                </form>
            </section>
        @endif
    </main>
</x-layouts.app>
