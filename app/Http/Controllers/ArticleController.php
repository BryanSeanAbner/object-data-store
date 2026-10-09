<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\MinioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Tampilan showcase 5 varian card artikel:
     * 1. Large card
     * 2. 2 Grid Column Card
     * 3. 3 Grid Column Card
     * 4. List Grid Column Card
     * 5. Asymmetric Card
     */
    public function index(): View
    {
        $articles = Article::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $items = $articles->map(fn (Article $article) => [
            'id' => $article->id,
            'title' => $article->title,
            'url' => '#',
            'image' => $article->photo_url,
            'category' => $article->category,
            'excerpt' => $article->description,
            'summary' => $article->description,
            'date' => $article->published_at?->translatedFormat('d F Y'),
            'time' => $article->published_at?->translatedFormat('d F Y'),
            'views' => rand(1, 9).'.'.rand(1, 9).'k views',
        ])->values()->all();

        return view('articles.index', [
            'articles' => $articles,
            'items' => $items,
        ]);
    }

    /**
     * Halaman form & kelola artikel (CRUD).
     */
    public function form(Request $request): View
    {
        $articles = Article::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return view('articles.form', [
            'articles' => $articles,
            'categories' => Article::CATEGORIES,
        ]);
    }

    public function store(Request $request, MinioService $minio): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadPhoto($request, $minio);
        }

        Article::create($data);

        return redirect()->route('articles.form')->with('status', 'Artikel berhasil ditambahkan.');
    }

    public function update(Request $request, Article $article, MinioService $minio): RedirectResponse
    {
        $data = $this->validated($request);

        // Hapus foto lama kalau diminta atau diganti foto baru.
        if ($article->photo && ($request->boolean('remove_photo') || $request->hasFile('photo'))) {
            $this->deletePhoto($article->photo, $minio);
            $data['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadPhoto($request, $minio);
        }

        $article->update($data);

        return redirect()->route('articles.form')->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article, MinioService $minio): RedirectResponse
    {
        if ($article->photo) {
            $this->deletePhoto($article->photo, $minio);
        }

        $article->delete();

        return redirect()->route('articles.form')->with('status', 'Artikel berhasil dihapus.');
    }

    /**
     * Upload foto artikel ke MinIO (dengan fallback ke local disk).
     */
    protected function uploadPhoto(Request $request, MinioService $minio): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $file = $request->file('photo');
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $key = date('Y/m/d').'/'.(string) Str::uuid().'.'.$ext;

        if ($minio->put($file, $key)) {
            return $key;
        }

        return $file->store('articles', 'public');
    }

    /**
     * Hapus foto artikel dari MinIO dan local disk jika ada.
     */
    protected function deletePhoto(?string $photo, MinioService $minio): void
    {
        if (! $photo) {
            return;
        }

        $minio->delete($photo);
        Storage::disk('public')->delete($photo);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'published_at' => ['required', 'date'],
            'category' => ['required', Rule::in(Article::CATEGORIES)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // File ditangani terpisah di store/update.
        unset($data['photo']);

        return $data;
    }
}
