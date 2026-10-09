<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

/**
 * MinIO S3-compatible client menggunakan Guzzle + AWS Signature V4.
 * Tidak memerlukan package aws-sdk; hanya butuh Guzzle (sudah include di Laravel).
 */
class MinioService
{
    protected Client $http;

    protected string $endpoint;

    protected string $accessKey;

    protected string $secretKey;

    protected string $bucket;

    protected string $region;

    public function __construct()
    {
        $this->endpoint = rtrim(config('minio.endpoint', 'http://localhost:9000'), '/');
        $this->accessKey = config('minio.access_key', '');
        $this->secretKey = config('minio.secret_key', '');
        $this->bucket = config('minio.bucket', 'articles');
        $this->region = config('minio.region', 'us-east-1');

        $this->http = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false,
        ]);
    }

    /**
     * Pastikan bucket target sudah ada di MinIO, jika belum maka buat secara otomatis.
     * Setelah buat, langsung set public-read policy.
     */
    public function ensureBucket(): bool
    {
        $path = '/'.$this->bucket;
        try {
            // Cek apakah bucket sudah ada via HEAD /{bucket}
            $res = $this->sendSignedRequest('HEAD', $path);
            if ($res->getStatusCode() === 200) {
                return true;
            }
        } catch (\Throwable $e) {
            // Bucket belum ada, coba buat via PUT /{bucket}
            try {
                $createRes = $this->sendSignedRequest('PUT', $path);
                if ($createRes->getStatusCode() === 200) {
                    $this->setPublicReadPolicy();

                    return true;
                }

                return false;
            } catch (\Throwable $createEx) {
                Log::error('MinIO createBucket error: '.$createEx->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Set bucket policy ke public-read agar gambar bisa diakses tanpa autentikasi.
     */
    public function setPublicReadPolicy(): bool
    {
        $policy = json_encode([
            'Version' => '2012-10-17',
            'Statement' => [[
                'Effect' => 'Allow',
                'Principal' => ['AWS' => ['*']],
                'Action' => ['s3:GetObject'],
                'Resource' => ['arn:aws:s3:::'.$this->bucket.'/*'],
            ]],
        ]);

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = $now->format('Ymd');
        $datetime = $now->format('Ymd\THis\Z');
        $payloadHash = hash('sha256', $policy);

        $parsedUrl = parse_url($this->endpoint);
        $host = $parsedUrl['host'].(isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '');

        $canonicalUri = '/'.$this->bucket;
        $canonicalQueryString = 'policy=';

        $sigHeaders = [
            'content-type' => 'application/json',
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $datetime,
        ];
        ksort($sigHeaders);

        $canonicalHeaders = '';
        $signedHeadersList = [];
        foreach ($sigHeaders as $k => $v) {
            $canonicalHeaders .= $k.':'.$v."\n";
            $signedHeadersList[] = $k;
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = implode("\n", ['PUT', $canonicalUri, $canonicalQueryString, $canonicalHeaders, $signedHeaders, $payloadHash]);
        $scope = $date.'/'.$this->region.'/s3/aws4_request';
        $stringToSign = implode("\n", ['AWS4-HMAC-SHA256', $datetime, $scope, hash('sha256', $canonicalRequest)]);

        $signingKey = $this->deriveSigningKey($date);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $this->accessKey, $scope, $signedHeaders, $signature
        );

        try {
            $this->http->put($this->endpoint.$canonicalUri.'?policy', [
                'headers' => [
                    'Authorization' => $authorization,
                    'Content-Type' => 'application/json',
                    'Host' => $host,
                    'x-amz-content-sha256' => $payloadHash,
                    'x-amz-date' => $datetime,
                ],
                'body' => $policy,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('MinIO setPublicReadPolicy skipped: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Dapatkan daftar nama bucket yang tersedia di MinIO.
     *
     * @return array<int, string>
     */
    public function listBuckets(): array
    {
        try {
            $res = $this->sendSignedRequest('GET', '/');
            $xml = simplexml_load_string((string) $res->getBody());
            $buckets = [];
            if ($xml && isset($xml->Buckets->Bucket)) {
                foreach ($xml->Buckets->Bucket as $b) {
                    $buckets[] = (string) $b->Name;
                }
            }

            return $buckets;
        } catch (\Throwable $e) {
            Log::error('MinIO listBuckets error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Upload file ke MinIO.
     *
     * @param  UploadedFile|string  $file  UploadedFile atau path string ke file lokal
     * @param  string  $objectKey  Nama objek di bucket, misal "articles/uuid.jpg"
     */
    public function put(UploadedFile|string $file, string $objectKey): bool
    {
        $this->ensureBucket();

        if ($file instanceof UploadedFile) {
            $body = file_get_contents($file->getRealPath());
            $contentType = $file->getMimeType() ?: 'application/octet-stream';
        } else {
            $body = file_get_contents($file);
            $contentType = (function_exists('mime_content_type') ? @mime_content_type($file) : null) ?: 'application/octet-stream';
        }

        $path = '/'.$this->bucket.'/'.ltrim($objectKey, '/');

        try {
            $this->sendSignedRequest('PUT', $path, $body, $contentType);

            return true;
        } catch (RequestException $e) {
            Log::error('MinIO upload error', ['key' => $objectKey, 'message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Hapus objek dari MinIO.
     */
    public function delete(string $objectKey): bool
    {
        $path = '/'.$this->bucket.'/'.ltrim($objectKey, '/');

        try {
            $this->sendSignedRequest('DELETE', $path);

            return true;
        } catch (RequestException $e) {
            Log::error('MinIO delete error', ['key' => $objectKey, 'message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Ambil konten file dari MinIO sebagai string binary.
     */
    public function get(string $objectKey): ?string
    {
        $path = '/'.$this->bucket.'/'.ltrim($objectKey, '/');

        try {
            $response = $this->sendSignedRequest('GET', $path);

            return (string) $response->getBody();
        } catch (RequestException $e) {
            Log::error('MinIO get error', ['key' => $objectKey, 'message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Cek apakah objek ada di MinIO.
     */
    public function exists(string $objectKey): bool
    {
        $path = '/'.$this->bucket.'/'.ltrim($objectKey, '/');

        try {
            $this->sendSignedRequest('HEAD', $path);

            return true;
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * URL publik objek di MinIO.
     * Strip awalan bucket-name dari objectKey agar tidak jadi double-prefix (backward-compat).
     */
    public function url(string $objectKey): string
    {
        $publicUrl = config('minio.public_url', $this->endpoint);

        // Strip accidental leading bucket prefix: "articles/2026/..." → "2026/..."
        $cleanKey = preg_replace('#^'.preg_quote($this->bucket, '#').'/+#', '', ltrim($objectKey, '/'));

        return rtrim($publicUrl, '/').'/'.$this->bucket.'/'.$cleanKey;
    }

    /**
     * Kirim HTTP request dengan AWS Signature V4.
     */
    protected function sendSignedRequest(
        string $method,
        string $canonicalUri,
        string $body = '',
        string $contentType = ''
    ): ResponseInterface {
        $url = $this->endpoint.$canonicalUri;
        $headers = $this->signRequest($method, $canonicalUri, $body, $contentType);

        $options = ['headers' => $headers];
        if ($body !== '') {
            $options['body'] = $body;
        }

        return $this->http->request($method, $url, $options);
    }

    /**
     * Buat header AWS Signature V4 untuk request ke MinIO.
     */
    protected function signRequest(
        string $method,
        string $canonicalUri,
        string $body,
        string $contentType
    ): array {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = $now->format('Ymd');
        $datetime = $now->format('Ymd\THis\Z');
        $payloadHash = hash('sha256', $body);

        $parsedUrl = parse_url($this->endpoint);
        $host = $parsedUrl['host'].(isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $datetime,
        ];

        if ($contentType !== '') {
            $headers['content-type'] = $contentType;
        }

        ksort($headers);

        $canonicalHeaders = '';
        $signedHeadersList = [];

        foreach ($headers as $key => $value) {
            $canonicalHeaders .= $key.':'.$value."\n";
            $signedHeadersList[] = $key;
        }

        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = implode("\n", [
            strtoupper($method),
            $canonicalUri,
            '',
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        $scope = $date.'/'.$this->region.'/s3/aws4_request';

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $datetime,
            $scope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->deriveSigningKey($date);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $this->accessKey,
            $scope,
            $signedHeaders,
            $signature
        );

        $resultHeaders = [
            'Authorization' => $authorization,
            'Host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $datetime,
        ];

        if ($contentType !== '') {
            $resultHeaders['Content-Type'] = $contentType;
        }

        return $resultHeaders;
    }

    /**
     * Turunkan signing key dari secret key menggunakan HMAC bertingkat.
     */
    protected function deriveSigningKey(string $date): string
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4'.$this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
