<?php

namespace App\Models;

use App\Services\MinioService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    public const CATEGORIES = [
        'Nasional',
        'Ekonomi',
        'Olahraga',
        'Hiburan',
        'Teknologi',
        'Internasional',
    ];

    protected $fillable = [
        'title',
        'description',
        'content',
        'published_at',
        'category',
        'photo',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    protected $appends = ['photo_url'];

    /** URL publik foto (MinIO / Public Storage), null kalau belum ada foto. */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (Storage::disk('public')->exists($this->photo)) {
            return Storage::disk('public')->url($this->photo);
        }

        return app(MinioService::class)->url($this->photo);
    }

    /** URL foto yang di-crop on-the-fly via arsitektur Thumbor. */
    public function thumborUrl(string $dimensions = '690x388', string $crop = '0x0:0x0'): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return url("/crop/{$crop}/{$dimensions}/{$this->photo}");
    }
}
