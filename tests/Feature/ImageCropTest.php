<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCropTest extends TestCase
{
    protected string $testFile;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::disk('public')->makeDirectory('tests');

        // Buat file master gambar 1200x800 menggunakan GD
        $im = imagecreatetruecolor(1200, 800);
        $bg = imagecolorallocate($im, 40, 80, 160);
        imagefill($im, 0, 0, $bg);

        $path = Storage::disk('public')->path('tests/master.jpg');
        imagejpeg($im, $path, 90);
        imagedestroy($im);

        $this->testFile = 'tests/master.jpg';
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->deleteDirectory('tests');
        parent::tearDown();
    }

    public function test_crop_endpoint_can_crop_to_square_95x95(): void
    {
        $response = $this->get('/crop/0x0:0x0/95x95/'.$this->testFile);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('X-Thumbor-Dimensions', '95x95');
        $this->assertNotEmpty($response->headers->get('ETag'));
    }

    public function test_crop_endpoint_can_crop_to_landscape_690x388(): void
    {
        $response = $this->get('/crop/0x0:0x0/690x388/'.$this->testFile);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('X-Thumbor-Dimensions', '690x388');
    }

    public function test_crop_endpoint_supports_http_304_caching(): void
    {
        $first = $this->get('/crop/0x0:0x0/95x95/'.$this->testFile);
        $etag = $first->headers->get('ETag');

        $second = $this->withHeaders(['If-None-Match' => $etag])
            ->get('/crop/0x0:0x0/95x95/'.$this->testFile);

        $second->assertStatus(304);
    }

    public function test_crop_endpoint_generates_placeholder_when_file_not_found(): void
    {
        $response = $this->get('/crop/0x0:0x0/400x200/missing-photo.jpg');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('X-Thumbor-Dimensions', '400x200');
    }
}
