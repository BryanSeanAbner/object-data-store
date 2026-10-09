<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Nusantara TV — Artikel')</title>

    @fonts

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-800">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-[1180px] items-center justify-between px-3 py-3 sm:px-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('articles.index') }}"
                    class="flex items-center gap-2 font-black text-blue-900 tracking-tight text-base sm:text-lg">
                    <span>Nusantara TV</span>
                </a>

                <nav class="hidden sm:flex items-center gap-1 text-[13px] font-bold">
                    <a href="{{ route('articles.index') }}"
                        class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('articles.index') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        View Article (5 Card)
                    </a>
                    <a href="{{ route('articles.form') }}"
                        class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs('articles.form') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Form / Kelola Artikel
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                @if (request()->routeIs('articles.index'))
                    <a href="{{ route('articles.form') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-800 px-3.5 py-1.5 text-[12px] font-bold text-white shadow-sm transition hover:bg-blue-900">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Form Articles
                    </a>
                @else
                    <a href="{{ route('articles.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-[12px] font-bold text-slate-700 transition hover:bg-slate-50">
                        &larr; View Articles
                    </a>
                @endif
            </div>
        </div>
    </header>

    @yield('content')

    @stack('scripts')
</body>

</html>