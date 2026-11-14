<x-layouts.app title="Log audit">
    <style>
        .al-wrap {
            max-width: 72rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .al-wrap {
                padding: 2.25rem 2rem 4rem;
            }
        }
        .al-panel {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .al-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }
        @media (min-width: 640px) {
            .al-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .al-filter-grid {
                grid-template-columns: 1fr 1fr 1fr auto;
                align-items: end;
            }
        }
        .al-input {
            margin-top: 0.375rem;
            min-height: 2.625rem;
            width: 100%;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0 0.875rem;
            font-size: 0.875rem;
            color: #1e293b;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .al-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        .al-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.625rem;
            padding: 0 1.25rem;
            border-radius: 0.625rem;
            border: none;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .al-btn:hover {
            background-color: #047857;
        }
        .al-table-box {
            margin-top: 1.5rem;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .al-head-row {
            display: none;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-align: left;
        }
        .al-log-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
            transition: background-color 0.14s ease;
        }
        .al-log-row:last-child {
            border-bottom: none;
        }
        .al-log-row:hover {
            background-color: #f8fafc;
        }
        @media (min-width: 768px) {
            .al-head-row,
            .al-log-row {
                grid-template-columns: 11.5rem minmax(0, 1fr) 13rem;
                align-items: center;
                gap: 1.25rem;
            }
            .al-head-row {
                display: grid;
            }
        }
        .al-time {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8125rem;
            color: #64748b;
            font-variant-numeric: tabular-nums;
        }
        .al-event {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            margin: 0;
            text-align: left;
        }
        .al-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 0.375rem;
            border: 1px solid #d1fae5;
            background-color: #f0fdf4;
            color: #065f46;
            padding: 0.15rem 0.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .al-entity {
            font-size: 0.8125rem;
            color: #475569;
            word-break: break-word;
        }
        .al-actor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            justify-content: flex-start;
        }
        .al-avatar {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 9999px;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #047857;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .al-actor-name {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #334155;
            word-break: break-all;
        }
    </style>

    <main id="main" class="al-wrap">
        <header class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Akses terbatas · Super Admin</span>
            </p>
            <h1 class="mt-1.5 text-2xl font-bold tracking-tight text-slate-800">Log audit</h1>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                Catatan hanya-baca. Nilai sensitif dan metadata permintaan mentah tidak disimpan di sini.
            </p>
        </header>

        <section class="al-panel mt-6 p-4 sm:p-5" aria-label="Filter log audit">
            <form method="GET" class="al-filter-grid">
                <div class="min-w-0">
                    <label class="block text-xs font-semibold text-slate-600" for="action">Aksi</label>
                    <input class="al-input placeholder:text-slate-400" id="action" name="action" value="{{ $filters['action'] ?? '' }}" maxlength="120" placeholder="Contoh: login, update…">
                </div>

                <div class="min-w-0">
                    <label class="block text-xs font-semibold text-slate-600" for="entity_type">Jenis data</label>
                    <input class="al-input placeholder:text-slate-400" id="entity_type" name="entity_type" value="{{ $filters['entity_type'] ?? '' }}" maxlength="120" placeholder="Contoh: User, Region…">
                </div>

                <div class="min-w-0">
                    <label class="block text-xs font-semibold text-slate-600" for="date">Tanggal</label>
                    <input class="al-input" id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}">
                </div>

                <button class="al-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    Terapkan filter
                </button>
            </form>
        </section>

        <section class="al-table-box" aria-label="Daftar peristiwa audit">
            <div class="al-head-row" aria-hidden="true">
                <span>Waktu</span>
                <span>Peristiwa &amp; Objek Data</span>
                <span>Pelaku</span>
            </div>

            <div>
                @forelse($logs as $log)
                    <article class="al-log-row">
                        <time class="al-time" datetime="{{ $log->created_at->toIso8601String() }}">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </time>

                        <p class="al-event">
                            <span class="al-badge">{{ $log->action }}</span>
                            <span class="al-entity">{{ $log->entity_type }}{{ $log->entity_id ? ' #'.$log->entity_id : '' }}</span>
                        </p>

                        <div class="al-actor">
                            <span class="al-avatar" aria-hidden="true">
                                {{ mb_substr($log->actor?->username ?? 'S', 0, 1) }}
                            </span>
                            <p class="al-actor-name">
                                {{ $log->actor?->username ?? 'Sistem / belum masuk' }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-500">Belum ada peristiwa audit pada filter ini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="mt-5">{{ $logs->links() }}</div>
    </main>
</x-layouts.app>
