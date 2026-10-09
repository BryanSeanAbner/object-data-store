<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageCropService
{
    protected string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = storage_path('framework/cache/thumbor');
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Process image crop and resize.
     *
     * @return array{path: string, mime: string, etag: string, width: int, height: int}|null
     */
    public function process(string $crop, string $dimensions, string $relativePath): ?array
    {
        $sourcePath = $this->resolveSourcePath($relativePath);

        // Jika file asli tidak ditemukan, buat placeholder dinamis
        if (! $sourcePath || ! file_exists($sourcePath)) {
            return $this->generatePlaceholder($dimensions, $relativePath);
        }

        [$targetW, $targetH] = $this->parseDimensions($dimensions);
        if ($targetW <= 0 || $targetH <= 0) {
            return null;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $cacheKey = hash('xxh128', "{$sourcePath}|{$crop}|{$dimensions}|".filemtime($sourcePath));
        $cacheFile = "{$this->cacheDir}/{$cacheKey}.{$extension}";
        $mime = $this->getMimeType($extension);

        // Sajikan dari cache jika sudah pernah diproses
        if (file_exists($cacheFile)) {
            return [
                'path' => $cacheFile,
                'mime' => $mime,
                'etag' => $cacheKey,
                'width' => $targetW,
                'height' => $targetH,
            ];
        }

        $srcImage = $this->loadImage($sourcePath, $extension);
        if (! $srcImage) {
            return null;
        }

        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);

        // Hitung koordinat crop
        [$cropX, $cropY, $cropW, $cropH] = $this->calculateCrop($crop, $srcW, $srcH, $targetW, $targetH);

        // Buat canvas gambar baru sesuai dimensi target
        $dstImage = imagecreatetruecolor($targetW, $targetH);

        // Preservasi transparansi untuk PNG & WEBP
        if (in_array($extension, ['png', 'webp'])) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
            imagefill($dstImage, 0, 0, $transparent);
        }

        // Resample pixel secara akurat
        imagecopyresampled(
            $dstImage,
            $srcImage,
            0,
            0,
            $cropX,
            $cropY,
            $targetW,
            $targetH,
            $cropW,
            $cropH
        );

        $this->saveImage($dstImage, $cacheFile, $extension);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return [
            'path' => $cacheFile,
            'mime' => $mime,
            'etag' => $cacheKey,
            'width' => $targetW,
            'height' => $targetH,
        ];
    }

    /**
     * Hitung koordinat crop: auto (center crop) atau manual bounding box.
     *
     * @return array{0: int, 1: int, 2: int, 3: int} [cropX, cropY, cropW, cropH]
     */
    protected function calculateCrop(string $crop, int $srcW, int $srcH, int $targetW, int $targetH): array
    {
        // Manual crop: misal "100x50:800x600"
        if (preg_match('/^(\d+)x(\d+):(\d+)x(\d+)$/', $crop, $matches)) {
            $x1 = (int) $matches[1];
            $y1 = (int) $matches[2];
            $x2 = (int) $matches[3];
            $y2 = (int) $matches[4];

            if ($x2 > $x1 && $y2 > $y1 && $x1 < $srcW && $y1 < $srcH) {
                $cropX = max(0, $x1);
                $cropY = max(0, $y1);
                $cropW = min($srcW - $cropX, $x2 - $x1);
                $cropH = min($srcH - $cropY, $y2 - $y1);

                return [$cropX, $cropY, $cropW, $cropH];
            }
        }

        // Auto / Smart Center-Crop (0x0:0x0)
        $srcAspect = $srcW / $srcH;
        $targetAspect = $targetW / $targetH;

        if ($srcAspect > $targetAspect) {
            // Foto asli lebih lebar dari rasio target -> potong sisi kiri dan kanan
            $cropW = (int) round($srcH * $targetAspect);
            $cropH = $srcH;
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = 0;
        } elseif ($srcAspect < $targetAspect) {
            // Foto asli lebih tinggi dari rasio target -> potong sisi atas dan bawah
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetAspect);
            $cropX = 0;
            $cropY = (int) round(($srcH - $cropH) / 2);
        } else {
            // Rasio sama persis -> tidak ada bagian yang dipotong
            $cropX = 0;
            $cropY = 0;
            $cropW = $srcW;
            $cropH = $srcH;
        }

        return [$cropX, $cropY, $cropW, $cropH];
    }

    /**
     * Parse string dimensi seperti "95x95" atau "690x388".
     *
     * @return array{0: int, 1: int}
     */
    protected function parseDimensions(string $dimensions): array
    {
        if (preg_match('/^(\d+)x(\d+)$/', $dimensions, $m)) {
            $w = min(2560, max(1, (int) $m[1]));
            $h = min(2560, max(1, (int) $m[2]));

            return [$w, $h];
        }

        return [300, 200];
    }

    /**
     * Cari lokasi file fisik sumber gambar (dari disk lokal atau unduh dari MinIO).
     */
    protected function resolveSourcePath(string $relativePath): ?string
    {
        $clean = ltrim($relativePath, '/\\');

        $candidates = [
            storage_path("app/public/{$clean}"),
            public_path("storage/{$clean}"),
            public_path($clean),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path) && is_file($path)) {
                return $path;
            }
        }

        // Cek apakah sudah pernah diunduh dan dicache dari MinIO
        $minioCachePath = storage_path("framework/cache/thumbor/sources/{$clean}");
        if (file_exists($minioCachePath) && is_file($minioCachePath)) {
            return $minioCachePath;
        }

        // Ambil dari MinIO Object Storage via MinioService
        try {
            /** @var MinioService $minio */
            $minio = app(MinioService::class);
            $content = $minio->get($clean);

            if ($content !== null) {
                $dir = dirname($minioCachePath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($minioCachePath, $content);

                return $minioCachePath;
            }
        } catch (\Throwable $e) {
            Log::warning("MinIO source fetch failed for {$clean}: ".$e->getMessage());
        }

        return null;
    }

    /**
     * Buat gambar placeholder elegan jika file gambar tidak ditemukan.
     *
     * @return array{path: string, mime: string, etag: string, width: int, height: int}
     */
    protected function generatePlaceholder(string $dimensions, string $label): array
    {
        [$w, $h] = $this->parseDimensions($dimensions);
        $cacheKey = hash('xxh128', "placeholder|{$w}|{$h}|{$label}");
        $cacheFile = "{$this->cacheDir}/{$cacheKey}.png";

        if (! file_exists($cacheFile)) {
            $im = imagecreatetruecolor($w, $h);

            // Gradien biru gelap Nusantara TV
            for ($y = 0; $y < $h; $y++) {
                $factor = $y / max(1, $h);
                $r = (int) (15 + $factor * 25);
                $g = (int) (23 + $factor * 35);
                $b = (int) (60 + $factor * 50);
                $col = imagecolorallocate($im, $r, $g, $b);
                imageline($im, 0, $y, $w, $y, $col);
            }

            $white = imagecolorallocate($im, 255, 255, 255);
            $cyan = imagecolorallocate($im, 147, 197, 253);

            $text1 = "{$w}x{$h}";
            $text2 = 'Nusantara TV';

            // Tulis teks jika ukuran cukup
            if ($w >= 70 && $h >= 50) {
                $font = 3;
                $fw = imagefontwidth($font);
                $fh = imagefontheight($font);
                $x1 = (int) max(5, ($w - strlen($text1) * $fw) / 2);
                $y1 = (int) max(5, ($h / 2) - $fh);
                imagestring($im, $font, $x1, $y1, $text1, $white);

                if ($h >= 90) {
                    $x2 = (int) max(5, ($w - strlen($text2) * $fw) / 2);
                    $y2 = (int) min($h - $fh - 5, ($h / 2) + 2);
                    imagestring($im, 2, $x2, $y2, $text2, $cyan);
                }
            }

            imagepng($im, $cacheFile, 8);
            imagedestroy($im);
        }

        return [
            'path' => $cacheFile,
            'mime' => 'image/png',
            'etag' => $cacheKey,
            'width' => $w,
            'height' => $h,
        ];
    }

    /**
     * Muat gambar GD dari file.
     *
     * @return \GdImage|resource|null
     */
    protected function loadImage(string $path, string $extension)
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path) ?: null,
            'png' => @imagecreatefrompng($path) ?: null,
            'webp' => @imagecreatefromwebp($path) ?: null,
            'gif' => @imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    /**
     * Simpan gambar GD ke file cache.
     *
     * @param  \GdImage|resource  $image
     */
    protected function saveImage($image, string $target, string $extension): void
    {
        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $target, 85),
            'png' => imagepng($image, $target, 8),
            'webp' => imagewebp($image, $target, 85),
            'gif' => imagegif($image, $target),
            default => imagejpeg($image, $target, 85),
        };
    }

    protected function getMimeType(string $extension): string
    {
        return match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
