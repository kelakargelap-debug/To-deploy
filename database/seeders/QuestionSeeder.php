<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ===== Tryout 1: SKB CPNS Umum — Latihan Dasar Komputer =====

        $q1 = Question::create([
            'tryout_id' => 1,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Protokol apa yang digunakan untuk mengirim email dari email klien ke email server?',
            'explanation' => 'SMTP (Simple Mail Transfer Protocol) digunakan untuk mengirim email. Sementara POP3 atau IMAP digunakan untuk menerima email.',
            'points' => 20,
            'order' => 1,
        ]);

        Option::insert([
            ['question_id' => $q1->id, 'label' => 'A', 'content' => 'SMTP', 'is_correct' => true, 'order' => 1],
            ['question_id' => $q1->id, 'label' => 'B', 'content' => 'POP3', 'is_correct' => false, 'order' => 2],
            ['question_id' => $q1->id, 'label' => 'C', 'content' => 'IMAP', 'is_correct' => false, 'order' => 3],
            ['question_id' => $q1->id, 'label' => 'D', 'content' => 'HTTP', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q1->id, 'label' => 'E', 'content' => 'FTP', 'is_correct' => false, 'order' => 5],
        ]);

        $q2 = Question::create([
            'tryout_id' => 1,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Manakah di bawah ini yang merupakan sistem operasi open source?',
            'explanation' => 'Linux adalah sistem operasi open-source, sedangkan Windows dan MacOS adalah proprietary.',
            'points' => 20,
            'order' => 2,
        ]);

        Option::insert([
            ['question_id' => $q2->id, 'label' => 'A', 'content' => 'Windows 11', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q2->id, 'label' => 'B', 'content' => 'MacOS Sequoia', 'is_correct' => false, 'order' => 2],
            ['question_id' => $q2->id, 'label' => 'C', 'content' => 'Linux Ubuntu', 'is_correct' => true, 'order' => 3],
            ['question_id' => $q2->id, 'label' => 'D', 'content' => 'iOS', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q2->id, 'label' => 'E', 'content' => 'MS-DOS', 'is_correct' => false, 'order' => 5],
        ]);

        $q3 = Question::create([
            'tryout_id' => 1,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Struktur data yang menganut prinsip LIFO (Last In First Out) adalah...',
            'explanation' => 'Stack menggunakan metode LIFO (Last In First Out), sedangkan Queue menggunakan FIFO (First In First Out).',
            'points' => 20,
            'order' => 3,
        ]);

        Option::insert([
            ['question_id' => $q3->id, 'label' => 'A', 'content' => 'Queue (Antrian)', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q3->id, 'label' => 'B', 'content' => 'Tree (Pohon)', 'is_correct' => false, 'order' => 2],
            ['question_id' => $q3->id, 'label' => 'C', 'content' => 'Stack (Tumpukan)', 'is_correct' => true, 'order' => 3],
            ['question_id' => $q3->id, 'label' => 'D', 'content' => 'Linked List', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q3->id, 'label' => 'E', 'content' => 'Array', 'is_correct' => false, 'order' => 5],
        ]);

        $q4 = Question::create([
            'tryout_id' => 1,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Perintah SQL untuk mengambil data unik (tidak ada duplikasi) dari suatu tabel adalah...',
            'explanation' => 'Keyword DISTINCT dalam perintah SELECT SQL digunakan untuk menghilangkan baris duplikat dari hasil pencarian.',
            'points' => 20,
            'order' => 4,
        ]);

        Option::insert([
            ['question_id' => $q4->id, 'label' => 'A', 'content' => 'SELECT UNIQUE', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q4->id, 'label' => 'B', 'content' => 'SELECT DISTINCT', 'is_correct' => true, 'order' => 2],
            ['question_id' => $q4->id, 'label' => 'C', 'content' => 'SELECT NOT_SAME', 'is_correct' => false, 'order' => 3],
            ['question_id' => $q4->id, 'label' => 'D', 'content' => 'SELECT DIFFERENT', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q4->id, 'label' => 'E', 'content' => 'SELECT FILTER_DUPLICATES', 'is_correct' => false, 'order' => 5],
        ]);

        $q5 = Question::create([
            'tryout_id' => 1,
            'type' => 'TRUE_FALSE',
            'content' => 'Apakah RAM (Random Access Memory) merupakan penyimpanan volatile (datanya terhapus saat listrik padam)?',
            'explanation' => 'Ya, RAM adalah penyimpanan volatile yang membutuhkan daya konstan untuk mempertahankan datanya. Berbeda dengan ROM atau Harddisk yang non-volatile.',
            'points' => 20,
            'order' => 5,
        ]);

        Option::insert([
            ['question_id' => $q5->id, 'label' => 'Benar', 'content' => 'Benar', 'is_correct' => true, 'order' => 1],
            ['question_id' => $q5->id, 'label' => 'Salah', 'content' => 'Salah', 'is_correct' => false, 'order' => 2],
        ]);

        // ===== Tryout 2: Premium SKB Pedagogik & Fungsional Guru =====

        $q6 = Question::create([
            'tryout_id' => 2,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Teori belajar yang menekankan bahwa belajar adalah usaha mengasosiasikan stimulus dan respon adalah...',
            'explanation' => 'Teori Behaviorisme berfokus pada hubungan antara Stimulus (S) dan Respon (R) serta penguatan perilaku.',
            'points' => 20,
            'order' => 1,
        ]);

        Option::insert([
            ['question_id' => $q6->id, 'label' => 'A', 'content' => 'Kognitivisme', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q6->id, 'label' => 'B', 'content' => 'Behaviorisme', 'is_correct' => true, 'order' => 2],
            ['question_id' => $q6->id, 'label' => 'C', 'content' => 'Konstruktivisme', 'is_correct' => false, 'order' => 3],
            ['question_id' => $q6->id, 'label' => 'D', 'content' => 'Humanisme', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q6->id, 'label' => 'E', 'content' => 'Sibernetik', 'is_correct' => false, 'order' => 5],
        ]);

        $q7 = Question::create([
            'tryout_id' => 2,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Salah satu pilar utama Kurikulum Merdeka adalah diferensiasi pembelajaran. Apa maksudnya?',
            'explanation' => 'Pembelajaran terdiferensiasi adalah pendekatan yang menyesuaikan materi, proses, dan produk belajar berdasarkan kesiapan, minat, dan profil belajar peserta didik.',
            'points' => 20,
            'order' => 2,
        ]);

        Option::insert([
            ['question_id' => $q7->id, 'label' => 'A', 'content' => 'Menyamakan standar semua siswa', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q7->id, 'label' => 'B', 'content' => 'Memberikan tugas yang sama dengan nilai berbeda', 'is_correct' => false, 'order' => 2],
            ['question_id' => $q7->id, 'label' => 'C', 'content' => 'Menyesuaikan proses belajar dengan kebutuhan murid', 'is_correct' => true, 'order' => 3],
            ['question_id' => $q7->id, 'label' => 'D', 'content' => 'Mengelompokkan murid berdasarkan status sosial', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q7->id, 'label' => 'E', 'content' => 'Fokus belajar di luar kelas saja', 'is_correct' => false, 'order' => 5],
        ]);

        $q8 = Question::create([
            'tryout_id' => 2,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Penilaian yang dilakukan di tengah atau akhir proses pembelajaran untuk mengukur penguasaan standar kompetensi adalah jenis penilaian...',
            'explanation' => 'Penilaian Sumatif dilakukan untuk menentukan tingkat pencapaian siswa di akhir unit pembelajaran, sedangkan formatif dilakukan untuk umpan balik selama proses.',
            'points' => 20,
            'order' => 3,
        ]);

        Option::insert([
            ['question_id' => $q8->id, 'label' => 'A', 'content' => 'Penilaian Diagnostik', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q8->id, 'label' => 'B', 'content' => 'Penilaian Formatif', 'is_correct' => false, 'order' => 2],
            ['question_id' => $q8->id, 'label' => 'C', 'content' => 'Penilaian Sumatif', 'is_correct' => true, 'order' => 3],
            ['question_id' => $q8->id, 'label' => 'D', 'content' => 'Penilaian Penempatan', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q8->id, 'label' => 'E', 'content' => 'Penilaian Portofolio', 'is_correct' => false, 'order' => 5],
        ]);

        $q9 = Question::create([
            'tryout_id' => 2,
            'type' => 'SINGLE_CHOICE',
            'content' => 'Landasan utama pendidik menyusun modul ajar dengan menggunakan scaffolding adalah...',
            'explanation' => 'Scaffolding didasarkan pada konsep Zone of Proximal Development (ZPD) oleh Vygotsky, di mana siswa dibantu pada awal proses belajar lalu perlahan dilepas.',
            'points' => 20,
            'order' => 4,
        ]);

        Option::insert([
            ['question_id' => $q9->id, 'label' => 'A', 'content' => 'Teori Perkembangan Piaget', 'is_correct' => false, 'order' => 1],
            ['question_id' => $q9->id, 'label' => 'B', 'content' => 'Zone of Proximal Development (Vygotsky)', 'is_correct' => true, 'order' => 2],
            ['question_id' => $q9->id, 'label' => 'C', 'content' => 'Hierarki Kebutuhan Maslow', 'is_correct' => false, 'order' => 3],
            ['question_id' => $q9->id, 'label' => 'D', 'content' => 'Teori Kondisioning Operan Skinner', 'is_correct' => false, 'order' => 4],
            ['question_id' => $q9->id, 'label' => 'E', 'content' => 'Taksonomi Bloom Terrevisi', 'is_correct' => false, 'order' => 5],
        ]);

        $q10 = Question::create([
            'tryout_id' => 2,
            'type' => 'TRUE_FALSE',
            'content' => 'Menurut regulasi asesmen saat ini, ketuntasan minimal belajar (KKM) mutlak digantikan kriteria ketercapaian tujuan pembelajaran (KKTP) yang disusun mandiri oleh satuan pendidikan.',
            'explanation' => 'Benar. Kurikulum Merdeka mengganti KKM dengan KKTP (Kriteria Ketercapaian Tujuan Pembelajaran) yang lebih holistik dan diserahkan kepada pendidik/sekolah.',
            'points' => 20,
            'order' => 5,
        ]);

        Option::insert([
            ['question_id' => $q10->id, 'label' => 'Benar', 'content' => 'Benar', 'is_correct' => true, 'order' => 1],
            ['question_id' => $q10->id, 'label' => 'Salah', 'content' => 'Salah', 'is_correct' => false, 'order' => 2],
        ]);
    }
}