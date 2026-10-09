<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\MinioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MinioStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_minio_service_generates_correct_public_url(): void
    {
        $minio = app(MinioService::class);
        $url = $minio->url('sample.jpg');

        $this->assertStringContainsString(config('minio.bucket').'/sample.jpg', $url);
        $this->assertStringStartsWith('http', $url);
    }

    public function test_minio_service_can_put_get_and_delete_object(): void
    {
        $minio = app(MinioService::class);
        $testKey = 'tests/minio-unit-test-'.uniqid().'.txt';
        $content = 'MinIO integration test '.time();

        // Buat file sementara
        $tempPath = storage_path('framework/cache/test_file.txt');
        file_put_contents($tempPath, $content);

        // Test PUT
        $putSuccess = $minio->put($tempPath, $testKey);
        $this->assertTrue($putSuccess);

        // Test EXISTS
        $this->assertTrue($minio->exists($testKey));

        // Test GET
        $fetched = $minio->get($testKey);
        $this->assertSame($content, $fetched);

        // Test DELETE
        $deleteSuccess = $minio->delete($testKey);
        $this->assertTrue($deleteSuccess);

        // Test EXISTS setelah DELETE
        $this->assertFalse($minio->exists($testKey));

        @unlink($tempPath);
    }

    public function test_article_store_uploads_photo_to_minio(): void
    {
        $file = UploadedFile::fake()->image('article-test.jpg', 640, 480);

        $response = $this->post(route('articles.store'), [
            'title' => 'Uji Coba Upload Foto MinIO',
            'description' => 'Deskripsi singkat pengujian upload objek ke MinIO bucket articles.',
            'content' => 'Konten lengkap artikel untuk menguji alur integrasi form artikel ke MinIO storage.',
            'published_at' => now()->toDateString(),
            'category' => 'Teknologi',
            'photo' => $file,
        ]);

        $response->assertRedirect(route('articles.form'));

        $article = Article::where('title', 'Uji Coba Upload Foto MinIO')->first();
        $this->assertNotNull($article);
        $this->assertNotNull($article->photo);
        $this->assertMatchesRegularExpression('#^\d{4}/\d{2}/\d{2}/#', $article->photo);

        // Cek bahwa objek benar-benar ada di MinIO
        $minio = app(MinioService::class);
        $this->assertTrue($minio->exists($article->photo));

        // Cek bahwa thumborUrl menghasilkan endpoint crop yang valid
        $cropUrl = $article->thumborUrl('95x95');
        $this->assertStringContainsString('/crop/0x0:0x0/95x95/', $cropUrl);

        // Test request ke endpoint crop untuk foto MinIO tersebut
        $cropResponse = $this->get($cropUrl);
        $cropResponse->assertStatus(200);
        $cropResponse->assertHeader('X-Thumbor-Dimensions', '95x95');

        // Bersihkan objek dari MinIO
        $minio->delete($article->photo);
    }
}
