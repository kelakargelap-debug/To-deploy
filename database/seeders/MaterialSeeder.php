<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Material::create([
            'category_id' => 1,
            'title' => 'Pengantar Komputasi Awan (Cloud Computing) - Konsep Dasar',
            'slug' => 'pengantar-komputasi-awan',
            'content' => <<<'HTML'
<h2>Pengantar Komputasi Awan</h2>
<p>Cloud computing adalah model penyediaan sumber daya komputasi (server, penyimpanan, database, jaringan, perangkat lunak) melalui internet ("awan") dengan pembayaran berbasis penggunaan.</p>

<h3>Model Layanan Cloud</h3>
<ul>
    <li><strong>IaaS (Infrastructure as a Service)</strong> — Menyediakan infrastruktur IT virtual seperti server, storage, dan jaringan. Pengguna mengelola OS dan aplikasi. Contoh: AWS EC2, Google Compute Engine, Azure VMs.</li>
    <li><strong>PaaS (Platform as a Service)</strong> — Menyediakan platform untuk mengembangkan, menjalankan, dan mengelola aplikasi tanpa mengelola infrastruktur di bawahnya. Contoh: Google App Engine, Heroku, AWS Elastic Beanstalk.</li>
    <li><strong>SaaS (Software as a Service)</strong> — Aplikasi siap pakai yang dihosting dan dikelola oleh penyedia. Pengguna tinggal memakai. Contoh: Google Workspace, Microsoft 365, Slack.</li>
</ul>

<h3>Karakteristik Utama</h3>
<ul>
    <li><strong>On-demand self-service</strong> — Pengguna dapat menyediakan sumber daya secara mandiri.</li>
    <li><strong>Broad network access</strong> — Dapat diakses melalui jaringan standar.</li>
    <li><strong>Resource pooling</strong> — Sumber daya dibagi antar banyak pengguna (multi-tenant).</li>
    <li><strong>Rapid elasticity</strong> — Kapasitas dapat dinaikkan/diturunkan secara cepat.</li>
    <li><strong>Measured service</strong> — Penggunaan terukur dan dapat dikenakan biaya sesuai pemakaian.</li>
</ul>
HTML,
            'required_tier' => 'FREE',
            'order' => 1,
            'is_published' => true,
        ]);

        Material::create([
            'category_id' => 2,
            'title' => '[PREMIUM] Panduan Menyusun Modul Ajar Kurikulum Merdeka',
            'slug' => 'panduan-modul-ajar-kurikulum-merdeka',
            'content' => <<<'HTML'
<h2>Panduan Menyusun Modul Ajar Kurikulum Merdeka</h2>
<p>Modul ajar adalah perangkat pembelajaran yang dirancang oleh pendidik untuk mencapai satu unit kompetensi atau sub-elemen dalam Kurikulum Merdeka. Modul ajar bersifat fleksibel dan dapat disesuaikan dengan konteks, karakteristik, dan kebutuhan peserta didik.</p>

<h3>Komponen Modul Ajar</h3>
<ol>
    <li><strong>Identitas Modul</strong> — Nama penyusun, institusi, tahun, jenjang, kelas, alokasi waktu.</li>
    <li><strong>Capaian Pembelajaran (CP)</strong> — Kompetensi yang harus dicapai peserta didik pada fase tertentu.</li>
    <li><strong>Tujuan Pembelajaran (TP)</strong> — Turunan dari CP yang lebih spesifik dan terukur.</li>
    <li><strong>Profil Pelajar Pancasila</strong> — Dimensi yang ingin dikembangkan (beriman, berkebinekaan global, gotong royong, mandiri, bernalar kritis, kreatif).</li>
    <li><strong>Sarana dan Prasarana</strong> — Media, alat, bahan, dan sumber belajar yang dibutuhkan.</li>
    <li><strong>Model Pembelajaran</strong> — Tatap muka, PJJ, blended, atau model lainnya.</li>
    <li><strong>Kegiatan Pembelajaran</strong> — Pendahuluan, inti (diferensiasi), dan penutup.</li>
    <li><strong>Asesmen</strong> — Diagnostik, formatif, dan sumatif.</li>
    <li><strong>Refleksi</strong> — Untuk pendidik dan peserta didik.</li>
</ol>

<h3>Prinsip Diferensiasi dalam Modul Ajar</h3>
<ul>
    <li><strong>Diferensiasi Konten</strong> — Menyesuaikan materi berdasarkan kesiapan, minat, dan profil belajar siswa.</li>
    <li><strong>Diferensiasi Proses</strong> — Variasi kegiatan dan strategi pembelajaran.</li>
    <li><strong>Diferensiasi Produk</strong> — Variasi bentuk hasil belajar atau tagihan.</li>
</ul>

<h3>Scaffolding dalam Modul Ajar</h3>
<p>Scaffolding adalah bantuan bertahap yang diberikan pendidik agar siswa dapat mencapai ZPD (Zone of Proximal Development) mereka. Bantuan dikurangi secara bertahap seiring meningkatnya kemandirian siswa.</p>
HTML,
            'required_tier' => 'PREMIUM',
            'order' => 1,
            'is_published' => true,
        ]);
    }
}