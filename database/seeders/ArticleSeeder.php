<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Services\MinioService;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dir = storage_path('app/public/photo/2026/10/07');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $samplePhoto = 'photo/2026/10/07/3634027902.jpg';
        $fullPath = storage_path("app/public/{$samplePhoto}");

        if (! file_exists($fullPath)) {
            $im = imagecreatetruecolor(1200, 800);
            for ($y = 0; $y < 800; $y++) {
                $r = (int) (18 + ($y / 800) * 160);
                $g = (int) (40 + ($y / 800) * 80);
                $b = (int) (160 - ($y / 800) * 110);
                $col = imagecolorallocate($im, $r, $g, $b);
                imageline($im, 0, $y, 1200, $y, $col);
            }
            $white = imagecolorallocate($im, 255, 255, 255);
            $cyan = imagecolorallocate($im, 147, 197, 253);
            imagestring($im, 5, 420, 370, 'NUSANTARA TV MASTER IMAGE 1200x800', $white);
            imagestring($im, 3, 490, 400, 'Original High-Res Asset', $cyan);
            imagejpeg($im, $fullPath, 92);
            imagedestroy($im);
        }

        // Simpan juga master image ke MinIO bucket 'articles'
        try {
            /** @var MinioService $minio */
            $minio = app(MinioService::class);
            if (! $minio->exists($samplePhoto)) {
                $minio->put($fullPath, $samplePhoto);
            }
        } catch (\Throwable $e) {
            // lanjutkan jika koneksi MinIO sedang offline
        }

        $articles = [
            [
                'title' => 'Presiden Resmikan Pusat Riset Teknologi Hijau Terbesar di Nusantara',
                'description' => 'Pusat riset inovasi energi bersih resmi beroperasi untuk mempercepat transisi energi hijau nasional menuju net zero emission 2060.',
                'content' => 'Pusat inovasi energi hijau nasional hari ini resmi dibuka oleh Presiden. Fasilitas seluas 40 hektare ini dilengkapi laboratorium riset sel surya generasi terbaru, teknologi penyimpanan hidrogen cair, serta inkubator startup teknologi iklim.',
                'published_at' => now()->toDateString(),
                'category' => 'Nasional',
                'photo' => $samplePhoto,
            ],
            [
                'title' => 'Ekonomi Kuartal Ini Tumbuh Solid Ditopang Lonjakan Investasi Digital',
                'description' => 'Pertumbuhan ekonomi Indonesia mencatatkan kinerja positif dengan kontribusi signifikan dari sektor perdagangan elektronik dan logistik terintegrasi.',
                'content' => 'Badan Pusat Statistik mencatat tren pertumbuhan ekonomi yang stabil dan melebihi ekspektasi pasar. Kenaikan konsumsi domestik serta geliat investasi di sektor ekonomi digital menjadi pendorong utama kestabilan fiskal saat ini.',
                'published_at' => now()->subDay()->toDateString(),
                'category' => 'Ekonomi',
                'photo' => null,
            ],
            [
                'title' => 'Timnas Indonesia Raih Kemenangan Gemilang di Putaran Kualifikasi Asia',
                'description' => 'Skuad Garuda tampil dominan di hadapan puluhan ribu suporter setia, mengamankan poin penuh lewat kerja sama tim yang solid dan taktis.',
                'content' => 'Pertandingan sengit antara Timnas Garuda dan tim tamu berakhir dengan sorak gembira pendukung tuan rumah. Strategi pressing ketat serta transisi serangan balik kilat berhasil membungkam pertahanan lawan sejak babak pertama.',
                'published_at' => now()->subDays(2)->toDateString(),
                'category' => 'Olahraga',
                'photo' => null,
            ],
            [
                'title' => 'Film Animasi Karya Sineas Tanah Air Raih Apresiasi Festival Film Internasional',
                'description' => 'Visual memukau berpadu dengan kekayaan cerita dongeng lokal berhasil memikat juri dan penonton dari berbagai negara di benua Eropa.',
                'content' => 'Dunia perfilman Indonesia kembali mencatatkan prestasi prestisius di kancah global. Animasi berdurasi 110 menit yang mengangkat nilai keberanian dan persahabatan ini mendapatkan standing ovation selama sepuluh menit dari para kritikus film.',
                'published_at' => now()->subDays(3)->toDateString(),
                'category' => 'Hiburan',
                'photo' => null,
            ],
            [
                'title' => 'Terobosan Kecerdasan Buatan Generasi Baru Hadir untuk Sektor Kesehatan Publik',
                'description' => 'Sistem diagnostik berbasis AI lokal mampu mendeteksi potensi penyakit dini hingga tingkat akurasi 98 persen di puskesmas pelosok.',
                'content' => 'Kementerian Kesehatan berkolaborasi dengan talenta AI dalam negeri meluncurkan perangkat lunak pemindaian rekam medis cerdas. Inovasi ini mempermudah tenaga medis melakukan deteksi risiko penyakit kronis tanpa harus merujuk pasien ke kota besar.',
                'published_at' => now()->subDays(4)->toDateString(),
                'category' => 'Teknologi',
                'photo' => null,
            ],
            [
                'title' => 'Konferensi Tingkat Tinggi Diplomasi Lingkungan Sepakati Komitmen Pemulihan Laut',
                'description' => 'Perwakilan dari 45 negara anggota menyetujui traktat perlindungan terumbu karang dan pengurangan limbah plastik lintas perairan internasional.',
                'content' => 'KTT Diplomasi Maritim yang diselenggarakan pekan ini membuahkan kesepakatan bersejarah. Program dana bersama pemulihan ekosistem laut dipastikan mulai bergulir efektif awal tahun mendatang guna melindungi keanekaragaman hayati.',
                'published_at' => now()->subDays(5)->toDateString(),
                'category' => 'Internasional',
                'photo' => null,
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['title' => $article['title']],
                $article
            );
        }
    }
}
