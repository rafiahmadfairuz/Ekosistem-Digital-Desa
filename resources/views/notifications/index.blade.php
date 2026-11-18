<x-layouts.app title="Pemberitahuan">
    <main id="main" class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                @php
                    $unreadCount = $notifications->filter(fn ($notification) => $notification->read_at === null)->count();
                @endphp
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Pemberitahuan
                    </h1>
                    @if ($unreadCount > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-emerald-600 to-teal-600 px-3 py-0.5 text-xs font-bold text-white shadow-xs tabular-nums">
                            <span class="size-1.5 rounded-full bg-amber-300" aria-hidden="true"></span>
                            <span>{{ $unreadCount }} belum dibaca</span>
                        </span>
                    @endif
                </div>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Pembaruan layanan, permohonan, dan transaksi yang terkait dengan akun Anda.
                </p>
            </div>

            @if ($unreadCount > 0)
                <div class="shrink-0">
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                        >
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 6 7 17l-5-5" />
                                <path d="m22 10-7.5 7.5L13 16" />
                            </svg>
                            <span>Tandai semua dibaca</span>
                        </button>
                    </form>
                </div>
            @endif
        </header>

        {{-- Daftar Pemberitahuan --}}
        @if ($notifications->isEmpty())
            <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                <x-ui.empty-state
                    title="Belum ada pemberitahuan"
                    description="Pembaruan yang terkait dengan akun Anda akan muncul di sini."
                />
            </div>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm" aria-label="Daftar pemberitahuan">
                <ul class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        @php
                            $isUnread = $notification->read_at === null;
                        @endphp
                        <li class="relative flex flex-col justify-between gap-4 p-5 transition-all duration-150 sm:flex-row sm:items-start sm:p-6 {{ $isUnread ? 'border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-50/90 via-teal-50/45 to-emerald-50/25 hover:from-emerald-100/75 hover:via-teal-50/65 hover:to-emerald-50/40' : 'bg-white hover:bg-slate-50/70' }}">
                            <div class="flex min-w-0 flex-1 items-start gap-3.5">
                                {{-- Ikon Status Pemberitahuan --}}
                                <span
                                    class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl border {{ $isUnread ? 'border-emerald-400/50 bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-sm shadow-emerald-600/20' : 'border-slate-200/80 bg-slate-50 text-slate-400' }}"
                                    aria-hidden="true"
                                >
                                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                    </svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="break-words text-sm font-bold leading-snug sm:text-base {{ $isUnread ? 'text-emerald-950' : 'text-slate-700' }}">
                                            {{ $notification->title }}
                                        </h2>

                                        @if ($isUnread)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-emerald-600 to-teal-600 px-2.5 py-0.5 text-[11px] font-bold text-white shadow-2xs">
                                                <span class="size-1.5 rounded-full bg-amber-300" aria-hidden="true"></span>
                                                <span>Baru</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-50 px-2.5 py-0.5 text-[11px] font-medium text-slate-500">
                                                Sudah dibaca
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 break-words text-xs leading-relaxed sm:text-sm {{ $isUnread ? 'font-medium text-slate-800' : 'text-slate-500' }}">
                                        {{ $notification->message }}
                                    </p>

                                    <time
                                        class="mt-2.5 block text-xs tabular-nums {{ $isUnread ? 'font-semibold text-emerald-700' : 'text-slate-400' }}"
                                        datetime="{{ $notification->created_at }}"
                                    >
                                        {{ \Illuminate\Support\Carbon::parse($notification->created_at)->translatedFormat('d M Y, H:i') }}
                                    </time>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('notifications.open', $notification->id) }}" class="shrink-0 pl-12.5 sm:pl-0">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ $notification->reference_type ? ($isUnread ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700' : 'border border-emerald-200 bg-white text-emerald-700 shadow-2xs hover:border-emerald-600 hover:bg-emerald-600 hover:text-white') : ($isUnread ? 'border border-emerald-300 bg-white text-emerald-800 shadow-2xs hover:border-emerald-600 hover:bg-emerald-600 hover:text-white' : 'border border-slate-200 bg-white text-slate-600 shadow-2xs hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900') }}"
                                >
                                    <span>{{ $notification->reference_type ? 'Buka pembaruan' : 'Tandai dibaca' }}</span>
                                    @if ($notification->reference_type)
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                @if ($notifications->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4 sm:px-6">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </section>
        @endif
    </main>
</x-layouts.app>
