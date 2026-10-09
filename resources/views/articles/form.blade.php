@extends('layouts.app')

@section('title', 'Kelola Artikel (Form) — Nusantara TV')

@section('content')
    @php
        // Kalau validasi gagal saat edit, ambil artikel yang sedang diedit supaya foto lama tetap tampil.
        $editingArticle = old('_id') ? \App\Models\Article::find(old('_id')) : null;
    @endphp

    <main class="min-h-screen bg-white text-slate-800">
        <div class="mx-auto w-full max-w-[1180px] px-2 py-8 sm:px-4">

            {{-- Header --}}
            <div
                class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-block rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-blue-800">
                            Manajemen Konten
                        </span>
                        <a href="{{ route('articles.index') }}"
                            class="text-[11px] font-bold text-slate-500 hover:text-blue-800 transition">
                            &larr; Lihat Tampilan Artikel
                        </a>
                    </div>
                    <h1 class="mt-1 text-[26px] font-black leading-tight text-blue-900 sm:text-[30px]">Form Kelola Artikel
                    </h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('articles.index') }}"
                        class="rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-50">
                        Lihat 5 Card
                    </a>
                    <button type="button" id="btn-create"
                        class="rounded-lg bg-blue-800 px-4 py-2 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-900">
                        + Tambah Artikel
                    </button>
                </div>
            </div>

            {{-- Flash message --}}
            @if (session('status'))
                <div
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[13px] font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Cari + filter kategori --}}
            <form method="GET" action="{{ route('articles.form') }}" class="mb-5 flex flex-col gap-2 sm:flex-row">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul artikel..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-[13px] focus:border-blue-700 focus:outline-none sm:max-w-xs">
                <select name="category"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-[13px] focus:border-blue-700 focus:outline-none">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-[13px] font-bold text-white transition hover:bg-slate-700">
                    Terapkan
                </button>
                @if (request()->hasAny(['q', 'category']))
                    <a href="{{ route('articles.form') }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-center text-[13px] font-bold text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Grid kartu kelola --}}
            @if ($articles->isNotEmpty())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $article)
                            <article
                                class="group flex w-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                                {{-- Rasio tetap 7/3 + object-cover: semua foto seragam walau rasio sumber beda --}}
                                <div class="relative aspect-[7/3] w-full shrink-0 overflow-hidden bg-slate-200">
                                    @if ($article->photo_url)
                                        <img src="{{ $article->photo_url }}" alt="{{ $article->title }}" loading="lazy"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    @else
                                        <div class="h-full w-full bg-gradient-to-br from-blue-950 via-slate-600 to-slate-400"></div>
                                    @endif
                                    <span
                                        class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black uppercase tracking-[0.5px] text-blue-800">
                                        {{ $article->category }}
                                    </span>
                                </div>

                                <div class="flex min-h-[110px] flex-1 flex-col justify-between gap-2 p-3">
                                    <div class="flex flex-col gap-1">
                                        <h3 class="m-0 line-clamp-2 text-[14px] font-black leading-snug text-black">
                                            {{ $article->title }}
                                        </h3>
                                        <p class="m-0 line-clamp-2 text-[12px] leading-relaxed text-slate-500">
                                            {{ $article->description }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between gap-2">
                                        <time class="text-[10px] font-bold tracking-[0.5px] text-slate-400">
                                            {{ $article->published_at?->translatedFormat('d F Y') }}
                                        </time>

                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                class="btn-edit rounded-md border border-slate-300 px-2.5 py-1 text-[11px] font-bold text-slate-700 transition hover:bg-slate-50"
                                                data-update-url="{{ route('articles.update', $article) }}" data-article="{{ json_encode([
                            'id' => $article->id,
                            'title' => $article->title,
                            'description' => $article->description,
                            'content' => $article->content,
                            'published_at' => $article->published_at?->format('Y-m-d'),
                            'category' => $article->category,
                            'photo_url' => $article->photo_url,
                        ]) }}">
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route('articles.destroy', $article) }}"
                                                onsubmit="return confirm('Hapus artikel ini? Tindakan tidak bisa dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-md border border-red-200 px-2.5 py-1 text-[11px] font-bold text-red-600 transition hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                    @endforeach
                </div>

                <div class="my-8">{{ $articles->links() }}</div>
            @else
                <div class="py-16 text-center">
                    <h2 class="text-lg font-bold text-slate-800">Belum ada artikel</h2>
                    <p class="mt-1 text-sm text-slate-500">Klik "Tambah Artikel" untuk membuat artikel baru.</p>
                </div>
            @endif
        </div>
    </main>

    {{-- ===================== MODAL FORM (Create & Edit) ===================== --}}
    {{-- ===================== MODAL FORM (Create & Edit) ===================== --}}
    <div id="article-modal"
        class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm">
        <div class="my-6 w-full max-w-4xl rounded-2xl bg-white p-5 shadow-2xl sm:p-7">
            <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-800 font-black text-xs">✍️</span>
                    <h2 id="modal-title" class="m-0 text-[18px] font-black text-blue-900">Tambah Artikel</h2>
                </div>
                <button type="button" id="btn-close"
                    class="rounded-lg p-1 text-2xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                    aria-label="Tutup">&times;</button>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-[12px] text-red-700">
                    <ul class="m-0 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="article-form" method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data"
                class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="_mode" id="form-mode" value="{{ old('_mode', 'create') }}">
                <input type="hidden" name="_id" id="form-id" value="{{ old('_id') }}">

                <div>
                    <label for="f-title" class="mb-1 block text-[12px] font-bold text-slate-700">Judul Artikel</label>
                    <input type="text" id="f-title" name="title" value="{{ old('title') }}" required maxlength="255"
                        placeholder="Masukkan judul berita yang menarik..."
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                </div>

                <div>
                    <label for="f-description" class="mb-1 block text-[12px] font-bold text-slate-700">Deskripsi Singkat
                        (Ringkasan)</label>
                    <textarea id="f-description" name="description" rows="2" required maxlength="500"
                        placeholder="Ringkasan 1-2 kalimat untuk ditampilkan di kartu artikel..."
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="f-content" class="mb-1 block text-[12px] font-bold text-slate-700">Konten Berita
                        Lengkap</label>
                    <textarea id="f-content" name="content" rows="6" required
                        placeholder="Tuliskan isi artikel berita selengkapnya..."
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">{{ old('content') }}</textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="f-category" class="mb-1 block text-[12px] font-bold text-slate-700">Kategori</label>
                        <select id="f-category" name="category" required
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                            <option value="">Pilih kategori berita</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="f-date" class="mb-1 block text-[12px] font-bold text-slate-700">Tanggal Terbit</label>
                        <input type="date" id="f-date" name="published_at" value="{{ old('published_at') }}" required
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-[13px] focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                    </div>
                </div>

                {{-- ================= FOTO & THUMBOR CROPPER STUDIO ================= --}}
                <div class="rounded-xl border border-blue-100 bg-slate-50/70 p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <label
                            class="flex items-center gap-2 text-[12px] font-black uppercase tracking-wider text-blue-900">
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded bg-blue-800 text-white text-[11px]">✂️</span>
                            <span>Foto Artikel & Thumbor Image Cropper</span>
                        </label>
                        <span class="text-[11px] font-medium text-slate-500">Maks 2 MB (JPG, PNG, WEBP)</span>
                    </div>

                    {{-- Input File Asli --}}
                    <input type="file" id="f-photo" name="photo" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-[12px] text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-blue-800 file:px-3 file:py-1.5 file:text-[12px] file:font-bold file:text-white hover:file:bg-blue-900 transition file:cursor-pointer cursor-pointer">

                    {{-- Status Banner Jika Crop Sudah Diterapkan --}}
                    <div id="applied-crop-bar"
                        class="mt-3 hidden items-center justify-between rounded-xl border border-emerald-300 bg-emerald-50/90 p-3 shadow-xs">
                        <div class="flex items-center gap-3">
                            <img id="applied-crop-thumb" src="" alt="Hasil Crop"
                                class="h-12 w-20 rounded-md border border-emerald-300 object-cover shadow-xs">
                            <div>
                                <p class="text-[12px] font-bold text-emerald-900 flex items-center gap-1.5">
                                    <span>✅</span>
                                    <span>Hasil Crop Berhasil Diterapkan</span>
                                </p>
                                <p class="text-[11px] text-emerald-700 font-medium" id="applied-crop-desc">
                                    Foto siap disimpan ke MinIO Object Storage
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-re-crop"
                                class="rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-[11px] font-bold text-emerald-800 shadow-2xs hover:bg-emerald-50 transition">
                                ✂️ Atur Ulang Crop
                            </button>
                        </div>
                    </div>

                    {{-- Workspace Cropper Studio Interaktif --}}
                    <div id="cropper-studio" class="mt-3 hidden rounded-xl border border-slate-300 bg-white p-3 shadow-sm">
                        {{-- Toolbar Alat --}}
                        <div
                            class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                            <span class="text-[11px] font-semibold text-slate-500">✂️ Sesuaikan area foto yang ingin
                                ditampilkan</span>
                            {{-- Tombol Kontrol Posisi --}}
                            <div class="flex flex-wrap items-center gap-1">
                                <button type="button" id="btn-crop-up" title="Geser ke Atas"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    ⬆️ Atas
                                </button>
                                <button type="button" id="btn-crop-down" title="Geser ke Bawah"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    ⬇️ Bawah
                                </button>
                                <button type="button" id="btn-crop-zoom-in" title="Perbesar"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    🔍+
                                </button>
                                <button type="button" id="btn-crop-zoom-out" title="Perkecil"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    🔍-
                                </button>
                                <button type="button" id="btn-crop-rotate" title="Putar 90 Derajat"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    🔄 Putar
                                </button>
                                <button type="button" id="btn-crop-reset" title="Kembalikan ke Awal"
                                    class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100 shadow-2xs">
                                    ↺ Reset
                                </button>
                            </div>
                        </div>

                        {{-- Canvas Cropper full-width --}}
                        <div class="w-full overflow-hidden rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center p-1"
                            style="height: 340px;">
                            <img id="cropper-image" src="" alt="Crop Editor" class="max-h-full max-w-full block">
                        </div>

                        {{-- Action Bar: Terapkan / Batalkan --}}
                        <div
                            class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-2.5">
                            <span class="text-[11px] font-semibold text-slate-500">
                                💡 Geser kotak pemotong untuk menentukan bagian foto yang ingin difokuskan.
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-cancel-crop"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-[12px] font-bold text-slate-700 hover:bg-slate-100 transition">
                                    Gunakan Foto Utuh (Auto Center)
                                </button>
                                <button type="button" id="btn-apply-crop"
                                    class="rounded-lg bg-emerald-600 px-4 py-1.5 text-[12px] font-bold text-white shadow-sm hover:bg-emerald-700 transition flex items-center gap-1.5">
                                    <span>✂️</span>
                                    <span>Terapkan Hasil Crop</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Preview foto lama saat edit (jika belum ada foto baru diunggah) --}}
                    <div id="existing-photo-wrap" class="mt-2 hidden">
                        <div
                            class="aspect-[7/3] w-full max-w-sm overflow-hidden rounded-lg bg-slate-200 border border-slate-300">
                            <img id="existing-photo-preview" src="" alt="Foto saat ini" class="h-full w-full object-cover">
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <button type="button" id="btn-edit-existing-crop"
                                class="rounded-md border border-blue-300 bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-800 hover:bg-blue-100 transition">
                                ✂️ Sesuaikan / Crop Foto Ini
                            </button>
                            <label id="remove-photo-wrap" class="flex items-center gap-1.5 text-[12px] text-slate-600">
                                <input type="checkbox" name="remove_photo" value="1" id="f-remove-photo">
                                Hapus foto ini
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" id="btn-cancel"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-[13px] font-bold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit"
                        class="rounded-lg bg-blue-800 px-5 py-2 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-900">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}">
        <style>
            .cropper-view-box,
            .cropper-face {
                border-radius: 4px;
            }

            .cropper-line,
            .cropper-point {
                background-color: #2563eb;
            }

            .cropper-view-box {
                outline: 2px solid #2563eb;
                outline-color: rgba(37, 99, 235, 0.75);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
        <script>
            (() => {
                const modal = document.getElementById('article-modal');
                const form = document.getElementById('article-form');
                const title = document.getElementById('modal-title');
                const submitBtn = document.getElementById('btn-submit');
                const methodInput = document.getElementById('form-method');
                const modeInput = document.getElementById('form-mode');
                const idInput = document.getElementById('form-id');
                const photoInput = document.getElementById('f-photo');

                // Cropper Elements
                const cropperStudio = document.getElementById('cropper-studio');
                const cropperImage = document.getElementById('cropper-image');
                const appliedBar = document.getElementById('applied-crop-bar');
                const appliedThumb = document.getElementById('applied-crop-thumb');
                const appliedDesc = document.getElementById('applied-crop-desc');
                const btnReCrop = document.getElementById('btn-re-crop');
                const btnApplyCrop = document.getElementById('btn-apply-crop');
                const btnCancelCrop = document.getElementById('btn-cancel-crop');
                // Existing Photo Elements
                const existingWrap = document.getElementById('existing-photo-wrap');
                const existingImg = document.getElementById('existing-photo-preview');
                const btnEditExisting = document.getElementById('btn-edit-existing-crop');
                const removeWrap = document.getElementById('remove-photo-wrap');
                const removeCheck = document.getElementById('f-remove-photo');

                let cropperInstance = null;
                let originalFile = null;
                const activeRatio = 1.777778; // 16:9 locked ratio

                const storeUrl = @json(route('articles.store'));
                const updateUrlTemplate = @json(route('articles.update', ':id'));

                const fields = {
                    title: document.getElementById('f-title'),
                    description: document.getElementById('f-description'),
                    content: document.getElementById('f-content'),
                    category: document.getElementById('f-category'),
                    published_at: document.getElementById('f-date'),
                };

                const showModal = () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                };

                const hideModal = () => {
                    destroyCropper();
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                };

                const destroyCropper = () => {
                    if (cropperInstance) {
                        cropperInstance.destroy();
                        cropperInstance = null;
                    }
                    cropperStudio.classList.add('hidden');
                };

                const initCropper = (imageSrc) => {
                    destroyCropper();
                    cropperImage.src = imageSrc;
                    cropperStudio.classList.remove('hidden');
                    appliedBar.classList.add('hidden');
                    appliedBar.classList.remove('flex');
                    existingWrap.classList.add('hidden');

                    cropperImage.onload = () => {
                        cropperInstance = new Cropper(cropperImage, {
                            aspectRatio: activeRatio,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.9,
                            restore: false,
                            preview: '.cropper-preview',
                            responsive: true,
                        });
                    };

                    // Trigger onload if already cached
                    if (cropperImage.complete) {
                        cropperImage.onload();
                    }
                };

                // Tombol Kontrol Cropper
                document.getElementById('btn-crop-up')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.move(0, -20);
                });
                document.getElementById('btn-crop-down')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.move(0, 20);
                });
                document.getElementById('btn-crop-zoom-in')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.zoom(0.1);
                });
                document.getElementById('btn-crop-zoom-out')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.zoom(-0.1);
                });
                document.getElementById('btn-crop-rotate')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.rotate(90);
                });
                document.getElementById('btn-crop-reset')?.addEventListener('click', () => {
                    if (cropperInstance) cropperInstance.reset();
                });

                // Terapkan Hasil Crop (Konversi ke Blob dan gantikan ke photoInput)
                btnApplyCrop?.addEventListener('click', () => {
                    if (!cropperInstance) return;

                    const canvas = cropperInstance.getCroppedCanvas({
                        maxWidth: 1920,
                        maxHeight: 1080,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    if (!canvas) return;

                    canvas.toBlob((blob) => {
                        if (!blob) return;

                        const fileName = (originalFile ? originalFile.name.replace(/\.[^/.]+$/, '') : 'cropped_image') + '.jpg';
                        const croppedFile = new File([blob], fileName, { type: 'image/jpeg' });

                        // Replace input file via DataTransfer
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(croppedFile);
                            photoInput.files = dt.files;
                        } catch (e) {
                            console.warn('DataTransfer not fully supported:', e);
                        }

                        // Tampilkan status applied
                        appliedThumb.src = canvas.toDataURL('image/jpeg', 0.85);
                        appliedDesc.textContent = `${canvas.width}x${canvas.height} px siap diunggah ke MinIO`;
                        appliedBar.classList.remove('hidden');
                        appliedBar.classList.add('flex');
                        cropperStudio.classList.add('hidden');
                    }, 'image/jpeg', 0.9);
                });

                // Atur Ulang Crop
                btnReCrop?.addEventListener('click', () => {
                    if (originalFile) {
                        const reader = new FileReader();
                        reader.onload = (e) => initCropper(e.target.result);
                        reader.readAsDataURL(originalFile);
                    } else if (existingImg.src) {
                        initCropper(existingImg.src);
                    }
                });

                // Batalkan Crop / Gunakan Foto Utuh
                btnCancelCrop?.addEventListener('click', () => {
                    destroyCropper();
                    if (originalFile) {
                        // Reset input ke file asli
                        const dt = new DataTransfer();
                        dt.items.add(originalFile);
                        photoInput.files = dt.files;

                        appliedThumb.src = URL.createObjectURL(originalFile);
                        appliedDesc.textContent = `Foto asli utuh (Auto Center-Crop Thumbor 0x0:0x0)`;
                        appliedBar.classList.remove('hidden');
                        appliedBar.classList.add('flex');
                    }
                });

                // Ketika user memilih file baru
                photoInput.addEventListener('change', () => {
                    const file = photoInput.files[0];
                    if (!file) return;

                    originalFile = file;
                    removeCheck.checked = false;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        initCropper(e.target.result);
                    };
                    reader.readAsDataURL(file);
                });

                // Edit existing crop dari artikel yang sedang diedit
                btnEditExisting?.addEventListener('click', () => {
                    if (existingImg.src) {
                        initCropper(existingImg.src);
                    }
                });

                const setPhotoState = (url, allowRemove) => {
                    photoInput.value = '';
                    originalFile = null;
                    removeCheck.checked = false;
                    destroyCropper();
                    appliedBar.classList.add('hidden');
                    appliedBar.classList.remove('flex');

                    if (url) {
                        existingImg.src = url;
                        existingWrap.classList.remove('hidden');
                    } else {
                        existingImg.removeAttribute('src');
                        existingWrap.classList.add('hidden');
                    }
                    removeWrap.classList.toggle('hidden', !(url && allowRemove));
                    removeWrap.classList.toggle('flex', !!(url && allowRemove));
                };

                const setMode = (mode, id = '') => {
                    const isEdit = mode === 'edit';
                    modeInput.value = mode;
                    idInput.value = id;
                    methodInput.value = isEdit ? 'PUT' : 'POST';
                    form.action = isEdit ? updateUrlTemplate.replace(':id', id) : storeUrl;
                    title.textContent = isEdit ? 'Edit Artikel' : 'Tambah Artikel';
                    submitBtn.textContent = isEdit ? 'Simpan Perubahan' : 'Simpan';
                };

                // Buka modal mode tambah (form dikosongkan)
                document.getElementById('btn-create').addEventListener('click', () => {
                    setMode('create');
                    Object.values(fields).forEach((el) => (el.value = ''));
                    fields.published_at.value = new Date().toISOString().slice(0, 10);
                    setPhotoState(null, false);
                    showModal();
                });

                // Buka modal mode edit (form diisi data artikel)
                document.querySelectorAll('.btn-edit').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const article = JSON.parse(btn.dataset.article);
                        setMode('edit', article.id);
                        Object.keys(fields).forEach((key) => (fields[key].value = article[key] ?? ''));
                        setPhotoState(article.photo_url, true);
                        showModal();
                    });
                });

                ['btn-close', 'btn-cancel'].forEach((id) => document.getElementById(id).addEventListener('click', hideModal));
                modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideModal(); });

                // Validasi gagal: buka lagi modal dengan input lama
                @if ($errors->any())
                    setMode(@json(old('_mode', 'create')), @json(old('_id', '')));
                    setPhotoState(@json($editingArticle?->photo_url), {{ $editingArticle ? 'true' : 'false' }});
                    showModal();
                @endif
                                    })();
        </script>
    @endpush
@endsection