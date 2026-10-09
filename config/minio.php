<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MinIO / S3-compatible Object Storage
    |--------------------------------------------------------------------------
    |
    | Konfigurasi koneksi ke MinIO menggunakan protocol S3-compatible.
    | Nilai diambil dari environment variables agar aman dan portabel.
    |
    */

    'endpoint' => env('MINIO_ENDPOINT', 'http://localhost:9000'),
    'access_key' => env('MINIO_ACCESS_KEY', ''),
    'secret_key' => env('MINIO_SECRET_KEY', ''),
    'bucket' => env('MINIO_BUCKET', 'nusantaratv'),
    'region' => env('MINIO_REGION', 'us-east-1'),

    /*
    | URL publik untuk mengakses file. Jika MinIO di-expose via reverse proxy
    | atau CDN, ubah nilai ini agar URL yang dihasilkan mengarah ke domain publik.
    */
    'public_url' => env('MINIO_PUBLIC_URL', env('MINIO_ENDPOINT', 'http://localhost:9000')),
];
