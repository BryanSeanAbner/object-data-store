<?php

namespace App\Http\Controllers;

use App\Services\ImageCropService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImageCropController extends Controller
{
    public function __construct(
        protected ImageCropService $cropService
    ) {}

    /**
     * Endpoint arsitektur on-the-fly Image Resizer & Cropper ala Thumbor.
     * URL pattern: /crop/{crop}/{dimensions}/{path}
     * Contoh:
     * - /crop/0x0:0x0/95x95/articles/contoh.jpg
     * - /crop/0x0:0x0/690x388/photo/2026/10/07/news.jpg
     * - /crop/100x50:800x600/300x200/articles/contoh.jpg
     */
    public function crop(Request $request, string $crop, string $dimensions, string $path): Response
    {
        $result = $this->cropService->process($crop, $dimensions, $path);

        if (! $result) {
            abort(404, 'Gambar tidak ditemukan atau gagal diproses.');
        }

        $etag = '"'.$result['etag'].'"';

        // HTTP 304 Not Modified jika browser sudah memiliki cache
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $response = new BinaryFileResponse($result['path']);
        $response->headers->set('Content-Type', $result['mime']);
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        $response->headers->set('ETag', $etag);
        $response->headers->set('X-Thumbor-Dimensions', "{$result['width']}x{$result['height']}");

        return $response;
    }
}
