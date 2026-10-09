@props([
    'title' => null,
    'url' => '#',
    'image' => null,
    'category' => null,
    'date' => null,
    'time' => null,
    'excerpt' => null,
    'summary' => null,
    'duration' => null,
    'views' => null,
    'size' => 'small',
    'variant' => null,
    'items' => [],
    'heading' => 'Berita Terkait',
    'limit' => 3,
])

@php
    $effectiveVariant = strtolower(str_replace([' ', '_'], '-', (string) ($variant ?? $size)));
    $isGrid2 = $effectiveVariant === 'grid-2col';
    $isGrid3 = $effectiveVariant === 'grid-3col';
    $isLarge = in_array($effectiveVariant, ['large', 'grid-large', 'kartu-besar', 'besar']);
    $isVideo = in_array($effectiveVariant, ['video', 'video-terbaru']);
    $isList = in_array($effectiveVariant, ['list', 'horizontal']);
    $isAsymmetric = in_array($effectiveVariant, ['asymmetric-grid', 'asymmetric', 'grid-asymmetric']);
    $isSmall = !$isLarge && !$isVideo && !$isList && !$isGrid2 && !$isGrid3 && !$isAsymmetric;

    // Interoperabilitas fleksibel antara excerpt vs summary dan date vs time
    $effectiveExcerpt = $excerpt ?? $summary;
    $effectiveDate = $date ?? $time;
@endphp

{{-- =========================================================================
VARIAN 1: GRID 2 KOLOM
========================================================================= --}}
@if ($isGrid2)
    @php $sliced2 = array_slice(collect($items)->toArray(), 0, PHP_INT_MAX); @endphp
    @if (!empty($sliced2))
        <div {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 gap-3']) }}>
            @foreach ($sliced2 as $g2item)
                @php
                    $g2t = $g2item['title'] ?? '';
                    $g2u = $g2item['url'] ?? '#';
                    $g2i = $g2item['image'] ?? null;
                    $g2c = $g2item['category'] ?? null;
                    $g2e = $g2item['excerpt'] ?? ($g2item['summary'] ?? null);
                    $g2d = $g2item['date'] ?? ($g2item['time'] ?? null);
                    $g2v = $g2item['views'] ?? null;
                @endphp
                <a href="{{ $g2u }}"
                    class="group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans">

                    {{-- Gambar aspect-[7/3] --}}
                    <div class="relative aspect-[7/3] w-full shrink-0 overflow-hidden bg-slate-200">
                        @if ($g2i)
                            <img src="{{ $g2i }}" alt="{{ $g2t }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @else
                            <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
                        @endif

                        @if ($g2c)
                            <span
                                class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[0.5px]">
                                {{ strtoupper($g2c) }}
                            </span>
                        @endif
                    </div>

                    {{-- Teks min 90px --}}
                    <div class="min-h-[90px] p-3 flex flex-col justify-between gap-1">
                        <div class="flex flex-col gap-1">
                            <h3
                                class="m-0 text-[13px] font-black leading-snug text-black group-hover:text-slate-700 transition-colors line-clamp-2">
                                {{ $g2t }}
                            </h3>
                            @if ($g2e)
                                <p class="m-0 text-[11px] leading-relaxed text-slate-500 line-clamp-2">{{ $g2e }}</p>
                            @endif
                        </div>

                        @if ($g2d || $g2v)
                            <div class="text-[10px] font-bold tracking-[0.5px] text-slate-400">
                                @if ($g2v)
                                    <span>{{ $g2v }}</span>
                                    @if ($g2d)
                                        <span>&bull;</span>
                                    @endif
                                @endif
                                @if ($g2d)
                                    <time>{{ $g2d }}</time>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- =========================================================================
    VARIAN 2: GRID 3 KOLOM
    ========================================================================= --}}
@elseif ($isGrid3)
    @php $sliced3 = array_slice(collect($items)->toArray(), 0, (int) $limit); @endphp
    @if (!empty($sliced3))
        <section {{ $attributes->merge(['class' => 'mt-5']) }}>
            @if ($heading)
                <h2 class="mb-3 text-[14px] font-black text-slate-700">{{ $heading }}</h2>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($sliced3 as $g3item)
                    @php
                        $g3t = $g3item['title'] ?? '';
                        $g3u = $g3item['url'] ?? '#';
                        $g3i = $g3item['image'] ?? null;
                        $g3c = $g3item['category'] ?? null;
                        $g3e = $g3item['excerpt'] ?? ($g3item['summary'] ?? null);
                        $g3d = $g3item['date'] ?? ($g3item['time'] ?? null);
                        $g3v = $g3item['views'] ?? null;
                    @endphp
                    <a href="{{ $g3u }}"
                        class="group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans">

                        {{-- Gambar aspect-[7/3] --}}
                        <div class="relative aspect-[7/3] w-full shrink-0 overflow-hidden bg-slate-200">
                            @if ($g3i)
                                <img src="{{ $g3i }}" alt="{{ $g3t }}"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
                            @endif

                            @if ($g3c)
                                <span
                                    class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[0.5px]">
                                    {{ strtoupper($g3c) }}
                                </span>
                            @endif
                        </div>

                        {{-- Teks min 100px --}}
                        <div class="min-h-[100px] p-3 flex flex-col justify-between gap-1">
                            <div class="flex flex-col gap-1">
                                <h3
                                    class="m-0 text-[13px] font-black leading-snug text-black group-hover:text-slate-700 transition-colors line-clamp-2">
                                    {{ $g3t }}
                                </h3>
                                @if ($g3e)
                                    <p class="m-0 text-[11px] leading-relaxed text-slate-500 line-clamp-2">{{ $g3e }}</p>
                                @endif
                            </div>

                            @if ($g3d || $g3v)
                                <div class="text-[10px] font-bold tracking-[0.5px] text-slate-400">
                                    @if ($g3v)
                                        <span>{{ $g3v }}</span>
                                        @if ($g3d)
                                            <span>&bull;</span>
                                        @endif
                                    @endif
                                    @if ($g3d)
                                        <time>{{ $g3d }}</time>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- =========================================================================
    VARIAN 3: LIST — Horizontal Card
    ========================================================================= --}}
@elseif ($isList)
    <a href="{{ $url }}" {{ $attributes->merge(['class' => 'group flex items-start gap-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans']) }}>
        <div class="relative h-[130px] w-[180px] sm:w-[200px] shrink-0 overflow-hidden bg-slate-200 rounded-l-xl">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $title }}"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
            @endif
        </div>

        <div class="min-w-0 flex-1 py-3 pr-4 flex flex-col justify-center">
            @if ($category)
                <span class="text-[9px] font-black uppercase tracking-[1px] text-blue-600">
                    {{ strtoupper($category) }}
                </span>
            @endif

            <h3
                class="mt-1 m-0 text-[13px] font-black leading-snug text-black group-hover:text-slate-700 transition-colors line-clamp-2">
                {{ $title }}
            </h3>

            @if ($effectiveExcerpt)
                <p class="mt-1.5 text-[12px] leading-relaxed text-slate-500 line-clamp-2">
                    {{ $effectiveExcerpt }}
                </p>
            @endif

            @if ($effectiveDate || $views)
                <div class="mt-2 text-[9px] font-bold tracking-[1px] text-slate-400">
                    @if ($views)
                        <span>{{ $views }}</span>
                        @if ($effectiveDate)
                            <span>&bull;</span>
                        @endif
                    @endif
                    @if ($effectiveDate)
                        <time>{{ $effectiveDate }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>

    {{-- =========================================================================
    VARIAN ASYMMETRIC GRID — 1 Hero Card + 2 Stacked Cards
    ========================================================================= --}}
@elseif ($isAsymmetric)
    @php
        $asymItems = !empty($items) ? collect($items)->toArray() : [];
        if (empty($asymItems) && !empty($title)) {
            $asymItems = [
                [
                    'title' => $title,
                    'url' => $url,
                    'image' => $image,
                    'category' => $category,
                    'excerpt' => $effectiveExcerpt,
                    'date' => $effectiveDate,
                ]
            ];
        }
        $heroItem = $asymItems[0] ?? null;
        $sideItems = array_slice($asymItems, 1, 2);
    @endphp

    @if ($heroItem)
        <div {{ $attributes->merge(['class' => 'grid grid-cols-1 md:grid-cols-12 gap-4 font-sans']) }}>
            {{-- Main / Hero Card (Kiri: 7 kolom, teks di dalam foto) --}}
            @php
                $ht = $heroItem['title'] ?? '';
                $hu = $heroItem['url'] ?? '#';
                $hi = $heroItem['image'] ?? null;
                $hc = $heroItem['category'] ?? null;
                $he = $heroItem['excerpt'] ?? ($heroItem['summary'] ?? null);
                $hd = $heroItem['date'] ?? ($heroItem['time'] ?? null);
            @endphp
            <div class="{{ !empty($sideItems) ? 'md:col-span-7' : 'md:col-span-12' }} flex flex-col">
                <a href="{{ $hu }}"
                    class="group relative flex flex-col justify-end overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md min-h-[360px] sm:min-h-[420px] w-full p-5 sm:p-6">

                    {{-- Background Photo / Gradient Polos --}}
                    @if ($hi)
                        <img src="{{ $hi }}" alt="{{ $ht }}"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400">
                        </div>
                    @endif

                    {{-- Dark Overlay Halus agar warna gradien asli tetap tampak identik --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                    {{-- Category Badge --}}
                    @if ($hc)
                        <span
                            class="absolute left-5 top-5 z-10 rounded-full bg-white/95 px-3 py-1 text-[9px] font-black uppercase text-blue-900 tracking-[0.5px] shadow-sm">
                            {{ strtoupper($hc) }}
                        </span>
                    @endif

                    {{-- Judul, Deskripsi, dan Tanggal di dalam Foto --}}
                    <div class="relative z-10 flex flex-col gap-2">
                        <h3
                            class="m-0 text-[18px] sm:text-[22px] font-black leading-snug text-white group-hover:text-blue-200 transition-colors line-clamp-2">
                            {{ $ht }}
                        </h3>
                        @if ($he)
                            <p class="m-0 text-[12px] sm:text-[13px] leading-relaxed text-slate-200 line-clamp-3">
                                {{ $he }}
                            </p>
                        @endif
                        @if ($hd)
                            <time class="mt-1 text-[10px] font-bold tracking-[0.5px] text-slate-300">
                                {{ $hd }}
                            </time>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Side Cards Stack (Kanan: 5 kolom, teks di dalam foto) --}}
            @if (!empty($sideItems))
                <div class="md:col-span-5 flex flex-col gap-4">
                    @foreach ($sideItems as $sItem)
                        @php
                            $st = $sItem['title'] ?? '';
                            $su = $sItem['url'] ?? '#';
                            $si = $sItem['image'] ?? null;
                            $sc = $sItem['category'] ?? null;
                            $se = $sItem['excerpt'] ?? ($sItem['summary'] ?? null);
                            $sd = $sItem['date'] ?? ($sItem['time'] ?? null);
                        @endphp
                        <a href="{{ $su }}"
                            class="group relative flex flex-1 flex-col justify-end overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md min-h-[170px] sm:min-h-[200px] w-full p-4">

                            {{-- Background Photo / Gradient Polos --}}
                            @if ($si)
                                <img src="{{ $si }}" alt="{{ $st }}"
                                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400">
                                </div>
                            @endif

                            {{-- Dark Overlay Halus --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                            {{-- Category Badge --}}
                            @if ($sc)
                                <span
                                    class="absolute left-4 top-4 z-10 rounded-full bg-white/95 px-2.5 py-0.5 text-[8px] font-black uppercase text-blue-900 tracking-[0.5px] shadow-sm">
                                    {{ strtoupper($sc) }}
                                </span>
                            @endif

                            {{-- Judul, Deskripsi, dan Tanggal di dalam Foto --}}
                            <div class="relative z-10 flex flex-col gap-1">
                                <h3
                                    class="m-0 text-[14px] sm:text-[15px] font-black leading-snug text-white group-hover:text-blue-200 transition-colors line-clamp-2">
                                    {{ $st }}
                                </h3>
                                @if ($se)
                                    <p class="m-0 text-[11px] leading-relaxed text-slate-200 line-clamp-1">
                                        {{ $se }}
                                    </p>
                                @endif
                                @if ($sd)
                                    <time class="mt-0.5 text-[9px] font-bold tracking-[0.5px] text-slate-300">
                                        {{ $sd }}
                                    </time>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@elseif ($isVideo)
    @php
        $videoItems = !empty($items) ? collect($items)->toArray() : [];
    @endphp

    @if (!empty($videoItems))
        {{-- Video Grid (Batch) --}}
        <div {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4']) }}>
            @foreach ($videoItems as $vIndex => $vItem)
                @php
                    $vt = $vItem['title'] ?? '';
                    $vu = $vItem['url'] ?? url('/video-berita?video=' . $vIndex);
                    $vi = $vItem['image'] ?? null;
                    $vc = $vItem['category'] ?? null;
                    $vd = $vItem['date'] ?? ($vItem['time'] ?? null);
                    $vDur = $vItem['duration'] ?? null;
                    $vv = $vItem['views'] ?? null;
                @endphp
                <a href="{{ $vu }}" data-home-video-card
                    class="home-video-card group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans">
                    <div class="relative aspect-[4/5] w-full shrink-0 overflow-hidden bg-slate-200">
                        @if ($vi)
                            <img src="{{ $vi }}" alt="{{ $vt }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @else
                            <div class="h-full w-full bg-gradient-to-br from-blue-900 to-slate-400"></div>
                        @endif

                        @if ($vc)
                            <span
                                class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[0.5px]">
                                {{ strtoupper($vc) }}
                            </span>
                        @endif

                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span
                                class="w-10 h-10 rounded-full bg-black/45 group-hover:bg-black/65 backdrop-blur-md border border-white/20 flex items-center justify-center text-white transition-all transform group-hover:scale-110 shadow-lg">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 fill-white translate-x-0.5" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </span>
                        </div>

                        @if ($vDur)
                            <span
                                class="absolute bottom-1.5 right-1.5 rounded bg-black/70 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $vDur }}</span>
                        @endif
                    </div>

                    <div class="p-2.5 flex flex-col justify-between flex-1 min-h-[75px] gap-1">
                        <h3
                            class="m-0 text-[13px] sm:text-[14px] font-black leading-tight text-slate-900 group-hover:text-blue-800 transition-colors line-clamp-2">
                            {{ $vt }}
                        </h3>

                        @if ($vd || $vv)
                            <div class="text-[9px] font-bold tracking-[0.5px] text-slate-400">
                                @if ($vv)
                                    <span>{{ $vv }}</span>
                                    @if ($vd)
                                        <span>&bull;</span>
                                    @endif
                                @endif
                                @if ($vd)
                                    <time>{{ $vd }}</time>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @else
        {{-- Single Video Card --}}
        <a href="{{ $url }}" data-home-video-card {{ $attributes->merge(['class' => 'home-video-card group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans']) }}>
            <div class="relative aspect-[4/5] w-full shrink-0 overflow-hidden bg-slate-200">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $title }}"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-blue-900 to-slate-400"></div>
                @endif

                @if ($category)
                    <span
                        class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[0.5px]">
                        {{ strtoupper($category) }}
                    </span>
                @endif

                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span
                        class="w-10 h-10 rounded-full bg-black/45 group-hover:bg-black/65 backdrop-blur-md border border-white/20 flex items-center justify-center text-white transition-all transform group-hover:scale-110 shadow-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 fill-white translate-x-0.5" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </span>
                </div>

                @if ($duration)
                    <span
                        class="absolute bottom-1.5 right-1.5 rounded bg-black/70 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $duration }}</span>
                @endif
            </div>

            <div class="p-2.5 flex flex-col justify-between flex-1 min-h-[75px] gap-1">
                <h3
                    class="m-0 text-[13px] sm:text-[14px] font-black leading-tight text-slate-900 group-hover:text-blue-800 transition-colors line-clamp-2">
                    {{ $title }}
                </h3>

                @if ($effectiveDate || $views)
                    <div class="text-[9px] font-bold tracking-[0.5px] text-slate-400">
                        @if ($views)
                            <span>{{ $views }}</span>
                            @if ($effectiveDate)
                                <span>&bull;</span>
                            @endif
                        @endif
                        @if ($effectiveDate)
                            <time>{{ $effectiveDate }}</time>
                        @endif
                    </div>
                @endif
            </div>
        </a>
    @endif

    {{-- =========================================================================
    VARIAN 5: KARTU BESAR (LARGE / GRID-LARGE) — Hero Card
    ========================================================================= --}}
@elseif ($isLarge)
    @php $hasText = !empty($title); @endphp
    @if ($hasText)
        <a href="{{ $url }}" {{ $attributes->merge(['class' => 'group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans']) }}>
    @else
            <div {{ $attributes->merge(['class' => 'w-full overflow-hidden rounded-xl']) }}>
        @endif

            <div
                class="relative aspect-[7/3] w-full shrink-0 overflow-hidden bg-slate-200 {{ $hasText ? '' : 'rounded-xl' }}">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $title ?? '' }}"
                        class="h-full w-full object-cover {{ $hasText ? 'transition-transform duration-300 group-hover:scale-105' : '' }}">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
                @endif

                @if ($category)
                    <span
                        class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[1.5px]">
                        {{ strtoupper($category) }}
                    </span>
                @endif
            </div>

            @if ($hasText)
                <div class="p-4 flex flex-col justify-between flex-1 gap-2">
                    <div>
                        <h3
                            class="m-0 text-[18px] sm:text-[20px] font-black leading-snug text-black group-hover:text-slate-700 transition-colors line-clamp-2">
                            {{ $title }}
                        </h3>

                        @if ($effectiveExcerpt)
                            <p class="mt-1.5 text-[13px] sm:text-[14px] leading-relaxed text-slate-500 line-clamp-2">
                                {{ $effectiveExcerpt }}
                            </p>
                        @endif
                    </div>

                    @if ($effectiveDate || $views)
                        <div class="text-[10px] font-bold tracking-[1px] text-slate-400">
                            @if ($views)
                                <span>{{ $views }}</span>
                                @if ($effectiveDate)
                                    <span>&bull;</span>
                                @endif
                            @endif
                            @if ($effectiveDate)
                                <time>{{ $effectiveDate }}</time>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @if ($hasText)
                </a>
            @else
        </div>
    @endif

@else
    <a href="{{ $url }}" {{ $attributes->merge(['class' => 'group flex flex-col w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md font-sans']) }}>
        {{-- Gambar aspect-[7/3] --}}
        <div class="relative aspect-[7/3] w-full shrink-0 overflow-hidden bg-slate-200">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $title }}"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
            @endif

            @if ($category)
                <span
                    class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase text-blue-800 tracking-[0.5px]">
                    {{ strtoupper($category) }}
                </span>
            @endif
        </div>

        <div class="min-h-[90px] p-3 flex flex-col justify-between gap-1">
            <div class="flex flex-col gap-1">
                <h3
                    class="m-0 text-[13px] font-black leading-snug text-black group-hover:text-slate-700 transition-colors line-clamp-2">
                    {{ $title }}
                </h3>

                @if ($effectiveExcerpt)
                    <p class="m-0 text-[11px] leading-relaxed text-slate-500 line-clamp-2">{{ $effectiveExcerpt }}</p>
                @endif
            </div>

            @if ($effectiveDate || $views)
                <div class="text-[10px] font-bold tracking-[0.5px] text-slate-400">
                    @if ($views)
                        <span>{{ $views }}</span>
                        @if ($effectiveDate)
                            <span>&bull;</span>
                        @endif
                    @endif
                    @if ($effectiveDate)
                        <time>{{ $effectiveDate }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>
@endif
