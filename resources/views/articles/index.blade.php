@extends('layouts.app')

@section('title', 'Tampilan Artikel (5 Varian Card) — Nusantara TV')

@section('content')
    @php
        $fallbackItem = [
            'title' => 'Presiden Resmikan Pusat Riset Teknologi Hijau Terbesar di Nusantara',
            'url' => '#',
            'image' => null,
            'category' => 'Nasional',
            'excerpt' => 'Pusat riset inovasi energi bersih resmi beroperasi untuk mempercepat transisi energi hijau nasional menuju net zero emission 2060.',
            'date' => now()->translatedFormat('d F Y'),
            'views' => '2.4k views',
        ];

        $itemsList = !empty($items) ? $items : array_fill(0, 6, $fallbackItem);

        // Varian 1: Large Card (Item pertama)
        $largeItem = $itemsList[0] ?? $fallbackItem;

        // Varian 2: 2 Grid Column Card (2 item)
        $grid2Items = array_slice($itemsList, 0, 2);

        // Varian 3: 3 Grid Column Card (3 item)
        $grid3Items = array_slice($itemsList, 0, 3);

        // Varian 4: List Grid Column Card (Horizontal)
        $listItems = array_slice($itemsList, 0, 4);

        // Varian 5: Asymmetric Card (1 Hero + 2 Stacked Side Cards)
        $asymmetricItems = array_slice($itemsList, 0, 3);
    @endphp

    <main class="min-h-screen bg-slate-50 py-8 text-slate-800">
        <div class="mx-auto w-full max-w-[1180px] px-3 sm:px-4">

            {{-- Top Intro Banner --}}
            <div
                class="mb-8 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 p-6 text-white shadow-sm sm:p-8">
                <h1 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">
                    Tampilan Varian Card Artikel
                </h1>

                {{-- Jump Navigation Bar --}}
                <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Lompat Ke:</span>
                    <a href="#large-card"
                        class="rounded-lg bg-white/10 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-white/20">
                        1. Large Card
                    </a>
                    <a href="#grid-2col"
                        class="rounded-lg bg-white/10 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-white/20">
                        2. 2 Grid Column
                    </a>
                    <a href="#grid-3col"
                        class="rounded-lg bg-white/10 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-white/20">
                        3. 3 Grid Column
                    </a>
                    <a href="#list-card"
                        class="rounded-lg bg-white/10 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-white/20">
                        4. List Grid Column
                    </a>
                    <a href="#asymmetric-card"
                        class="rounded-lg bg-white/10 px-3 py-1 text-[11px] font-bold text-white transition hover:bg-white/20">
                        5. Assymetrics Card
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-10">

                {{-- =========================================================================
                NOMOR 1: LARGE CARD
                ========================================================================= --}}
                <section id="large-card"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 scroll-mt-20">
                    <div
                        class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="inline-block rounded bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase text-blue-800">
                                Nomor 1
                            </span>
                            <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">
                                Large Card (Hero Card)
                            </h2>
                            <p class="text-[12px] text-slate-500">
                                Kartu berita format besar dengan foto rasio 7/3 dan cuplikan deskripsi yang jelas.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">variant="large"</span>
                    </div>

                    <div class="max-w-3xl">
                        <x-article-card variant="large" :title="$largeItem['title']" :url="$largeItem['url']"
                            :image="$largeItem['image']" :category="$largeItem['category']" :excerpt="$largeItem['excerpt']"
                            :date="$largeItem['date']" :views="$largeItem['views']" />
                    </div>
                </section>

                {{-- =========================================================================
                NOMOR 2: 2 GRID COLUMN CARD
                ========================================================================= --}}
                <section id="grid-2col"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 scroll-mt-20">
                    <div
                        class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="inline-block rounded bg-indigo-100 px-2 py-0.5 text-[10px] font-black uppercase text-indigo-800">
                                Nomor 2
                            </span>
                            <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">
                                2 Grid Column Card
                            </h2>
                            <p class="text-[12px] text-slate-500">
                                Tata letak dua kartu berdampingan yang fleksibel untuk seksi berita utama ganda.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">variant="grid-2col"</span>
                    </div>

                    <x-article-card variant="grid-2col" :items="$grid2Items" />
                </section>

                {{-- =========================================================================
                NOMOR 3: 3 GRID COLUMN CARD
                ========================================================================= --}}
                <section id="grid-3col"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 scroll-mt-20">
                    <div
                        class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-800">
                                Nomor 3
                            </span>
                            <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">
                                3 Grid Column Card
                            </h2>
                            <p class="text-[12px] text-slate-500">
                                Tata letak 3 kolom untuk menampilkan daftar berita terpopuler atau berita terkait.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">variant="grid-3col"</span>
                    </div>

                    <x-article-card variant="grid-3col" heading="Berita Terkini Nusantara" :limit="3"
                        :items="$grid3Items" />
                </section>

                {{-- =========================================================================
                NOMOR 4: LIST GRID COLUMN CARD
                ========================================================================= --}}
                <section id="list-card"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 scroll-mt-20">
                    <div
                        class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="inline-block rounded bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase text-amber-800">
                                Nomor 4
                            </span>
                            <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">
                                List Grid Column Card (Horizontal)
                            </h2>
                            <p class="text-[12px] text-slate-500">
                                Format baris horizontal dengan thumbnail gambar di sisi kiri dan konten teks di sisi kanan.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">variant="list"</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5 lg:grid-cols-2">
                        @foreach ($listItems as $listItem)
                            <x-article-card variant="list" :title="$listItem['title']" :url="$listItem['url']"
                                :image="$listItem['image']" :category="$listItem['category']" :excerpt="$listItem['excerpt']"
                                :date="$listItem['date']" :views="$listItem['views']" />
                        @endforeach
                    </div>
                </section>

                {{-- =========================================================================
                NOMOR 5: ASSYMETRICS CARD
                ========================================================================= --}}
                <section id="asymmetric-card"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 scroll-mt-20">
                    <div
                        class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="inline-block rounded bg-purple-100 px-2 py-0.5 text-[10px] font-black uppercase text-purple-800">
                                Nomor 5
                            </span>
                            <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">
                                Assymetrics Card (1 Hero + 2 Stacked Cards)
                            </h2>
                            <p class="text-[12px] text-slate-500">
                                Komposisi asimetris dengan 1 kartu hero besar di kolom kiri dan 2 kartu bertumpuk di kolom
                                kanan dengan tipografi di atas foto.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">variant="asymmetric"</span>
                    </div>

                    <x-article-card variant="asymmetric" :items="$asymmetricItems" />
                </section>

            </div>

            {{-- Bottom Footer Info --}}
            <div class="mt-10 rounded-xl border border-slate-200 bg-white p-4 text-center text-xs text-slate-500 shadow-sm">
                <span>Ingin menambah atau mengedit artikel? Kunjungi </span>
                <a href="{{ route('articles.form') }}" class="font-bold text-blue-700 underline hover:text-blue-900">
                    Halaman Form Artikel
                </a>.
            </div>
        </div>
    </main>
@endsection