# PANDUAN PRESENTASI & RINGKASAN 11 UNIT KOMPETENSI SKKNI LSP P3
> **Skema Sertifikasi:** Pemrogram Junior (*Junior Programmer*)  
> **Lembaga Sertifikasi Profesi:** LSP Media Informatika (LSP P3) — Nomor Skema: `020405/SKM.LSPMI/I/2023`  
> **Aplikasi Acuan:** School E-Voting System (Pemilihan Terpadu OSIS/MPK Berbasis Web)  
> **Repository:** `fidzzz222/evoting` | **Stack:** Laravel 13.x | PHP 8.3+ | SQLite 3 | Tailwind CSS v4 | Alpine.js  

---

## 1. Lembar Pengantar & Strategi Menghadapi Asesor

Dokumen ini disusun sebagai **panduan taktis dan materi pertahanan (*defense cheat-sheet*)** saat menjalani Uji Kompetensi (UKOM) di hadapan Asesor Penguji LSP P3. Seluruh unit kompetensi yang tercantum dalam SKKNI Pemrogram Junior telah diimplementasikan secara konkret pada sistem E-Voting ini.

### 3 Prinsip Utama Menjawab Asesor:
1. **Berbasis Bukti (*Evidence-Based*):** Jangan sekadar menjawab secara teoritis. Tunjukkan langsung file konfigurasi, baris kode, atau antarmuka aplikasi di Visual Studio Code.
2. **Kuasai Alur Data (*Data Flow*):** Asesor menyukai peserta yang paham bagaimana sebuah data mengalir dari formulir HTML/Blade $\rightarrow$ Routing $\rightarrow$ Controller $\rightarrow$ Action/Service $\rightarrow$ Transaksi Database SQLite $\rightarrow$ Response JSON/Redirect.
3. **Pahami Reusability & Keamanan (*Best Practices*):** Tunjukkan bahwa aplikasi dibangun dengan arsitektur bersih (DRY - *Don't Repeat Yourself*), transaksi atomik (*ACID*), dan pencegahan suara ganda (*zero double-voting*).

---

## 2. Matriks Pemetaan 11 Unit Kompetensi SKKNI vs Implementasi Sistem

| No | Kode Unit | Judul Unit Kompetensi | Bukti Implementasi pada Sistem E-Voting | Status Bukti |
|:--:|:---|:---|:---|:---:|
| **1** | `J.612000.001.01` | Mengidentifikasi Platform Perangkat Lunak dan Bahasa Pemrograman | Platform: Windows/Linux OS, PHP 8.3 CLI/FPM, Node.js runtime. Bahasa: PHP, JavaScript, SQL, HTML5, CSS3. | `Valid` |
| **2** | `J.612000.007.01` | Merancang Antarmuka Pengguna untuk Aplikasi Mobile | Desain Mobile-Responsive via Tailwind CSS, Viewport Meta Tag, Card Paslon Touch-Friendly, Modal Dialog Mobile. | `Valid` |
| **3** | `J.620100.001.01` | Menganalisis Tools yang Digunakan untuk Membangun Perangkat Lunak | VS Code IDE, Git & GitHub Version Control, Composer, NPM, SQLite Viewer/TablePlus, Browser DevTools. | `Valid` |
| **4** | `J.620100.003.01` | Mengidentifikasi Library, Komponen, atau Framework yang Digunakan | Backend: Laravel 13.x, Sanctum, DomPDF. Frontend: Tailwind CSS v4, Alpine.js v3, Chart.js, Axios. | `Valid` |
| **5** | `J.620100.004.02` | Menerapkan Struktur Data | Array Asosiatif/Multidimensi, Laravel Collections (`map`, `filter`, `groupBy`), Model Casts (`array`/`json`). | `Valid` |
| **6** | `J.620100.005.02` | Menerapkan User Interface | Blade Templating Engine, Layout Admin & Pemilih, Form Login NISN/Token, Dialog Konfirmasi Pencoblosan. | `Valid` |
| **7** | `J.620100.007.01` | Menentukan Entitas dan Relasi Data dalam Basis Data | 7 Tabel Terelasi di SQLite (`users`, `events`, `candidates`, `voters`, `votes`, `event_participations`, `audit_logs`). Relasi Eloquent `hasMany`, `belongsTo`. | `Valid` |
| **8** | `J.620100.010.01` | Menulis Kode yang Mengeksekusi Teks, Grafik, dan Elemen Multimedia | Teks: Parsing CSV & format laporan. Grafik: Chart.js (Real-time Quick Count). Multimedia: Upload & render foto kandidat. | `Valid` |
| **9** | `J.620100.011.01` | Melakukan Instalasi Software Tools untuk Pemrograman | Instalasi PHP 8.3, Composer, Node.js/NPM, Git, Dependensi vendor via `composer install` & node_modules via `npm install`. | `Valid` |
| **10** | `J.620100.012.01` | Melakukan Pengaturan Software Tools untuk Pemrograman | Konfigurasi file `.env`, `config/database.php` (SQLite WAL mode), `config/auth.php` (Multi-Guard), `vite.config.js`. | `Valid` |
| **11** | `J.620100.014.01` | Menerapkan Penggunaan Kembali Subrutin-subrutin (*Reusability*) | **FOKUS KHUSUS**: Action Class `CastVoteAction`, Service `VoterImportService`, Helper `AuditLog::log()`, Reusable Blade Components. | `Valid & Teruji` |

---

## 3. Bedah Rinci Unit Kompetensi 1 s.d. 10

### Unit 1: Mengidentifikasi Platform Perangkat Lunak dan Bahasa Pemrograman (`J.612000.001.01`)
- **Konsep:** Kemampuan mengidentifikasi platform sistem operasi, lingkungan runtime eksekusi, serta bahasa pemrograman yang digunakan dalam aplikasi.
- **Implementasi pada Sistem:**
  - **Platform OS:** Platform pengembangan menggunakan Windows 10/11 x64, siap di-deploy ke Linux Server (Ubuntu/Debian) berbasis Docker atau Web Server standar (Nginx/Apache).
  - **Runtime Engine:** PHP 8.3+ (Zend Engine) untuk server-side backend, dan Node.js v20+ untuk bundling frontend assets.
  - **Bahasa Pemrograman:**
    - `PHP 8.3+`: Logika backend, MVC, ORM Eloquent, enkripsi token, penanganan transaksi database.
    - `JavaScript (ES6+)`: Interaktivitas klien, reaktivitas komponen Alpine.js, inisialisasi grafik Chart.js.
    - `SQL (SQLite dialect)`: Definisi skema tabel (DDL) dan manipulasi data transaksi (DML).
    - `HTML5 & CSS3`: Struktur tampilan berbasis Blade dan styling berbasis Tailwind CSS utility classes.
- **Cara Menjawab Asesor:**
  > *"Sistem E-Voting ini beroperasi pada platform web multiplatform dengan runtime PHP 8.3 server-side dan Node.js untuk kompilasi client-side. Bahasa utamanya adalah PHP modern berorientasi objek yang berjalan di atas framework Laravel, didukung JavaScript ES6 untuk reaktivitas antarmuka tanpa perlu membebani klien dengan arsitektur SPA yang berat."*

---

### Unit 2: Merancang Antarmuka Pengguna untuk Aplikasi Mobile (`J.612000.007.01`)
- **Konsep:** Merancang antarmuka sistem yang adaptif, responsif, dan ramah pengguna ketika diakses melalui perangkat layar kecil (*smartphone/tablet*).
- **Implementasi pada Sistem:**
  - **Viewport Meta:** Terpasang `<meta name="viewport" content="width=device-width, initial-scale=1.0">` pada master layout `resources/views/layouts/voter.blade.php`.
  - **Responsive Grid:** Daftar kartu kandidat menggunakan kelas grid dinamis Tailwind: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6`. Pada ponsel layar kecil (lebar < 640px), kartu paslon otomatis tersusun vertikal satu kolom.
  - **Touch-Friendly Buttons:** Tombol aksi pencoblosan dirancang dengan tinggi minimum 48px (`py-3 px-6 text-base font-semibold rounded-xl active:scale-95 transition`), sangat nyaman ditekan menggunakan ibu jari layar sentuh tanpa risiko salah tekan.
  - **Modal Konfirmasi Adaptif:** Modal popup konfirmasi pencoblosan menggunakan Alpine.js dengan penyesuaian margin dan scrolling otomatis pada layar sempit.
- **Cara Menjawab Asesor:**
  > *"Meskipun E-Voting ini aplikasi web, seluruh antarmuka bilik suara dirancang Mobile-First. Saat siswa mencoblos melalui HP Android atau iOS, antarmuka otomatis menyesuaikan layout menjadi satu kolom, tombol pilihan paslon memiliki ukuran sentuh yang ramah jari, dan modal konfirmasi mencegah salah coblos di layar sentuh."*

---

### Unit 3: Menganalisis Tools yang Digunakan untuk Membangun Perangkat Lunak (`J.620100.001.01`)
- **Konsep:** Memilih dan menganalisis perangkat lunak pembantu (alat pengembangan) yang sesuai dengan kebutuhan rekayasa perangkat lunak.
- **Implementasi pada Sistem:**
  - **Editor / IDE:** Visual Studio Code dengan ekstensi PHP Intelephense, Laravel Blade Formatter, Tailwind CSS IntelliSense.
  - **Version Control System (VCS):** Git untuk pelacakan revisi kode dan remote repository GitHub (`fidzzz222/evoting`).
  - **Package Manager:** Composer (manajemen library PHP) dan NPM (manajemen library CSS/JS).
  - **Database Management Tool:** TablePlus / SQLite CLI / VS Code SQLite Viewer untuk memeriksa integritas foreign key dan isi tabel.
  - **API Client:** Postman / cURL untuk menguji endpoint RESTful API voter dan admin.
  - **Browser DevTools:** Chrome/Edge Developer Tools untuk memeriksa Network Request (Axios), konsol JavaScript, dan audit CSS.
- **Cara Menjawab Asesor:**
  > *"Saya menggunakan rangkaian tools standar industri: Visual Studio Code sebagai IDE utama, Composer dan NPM untuk resolusi dependensi, Git untuk versioning, serta browser devtools dan cURL untuk pengujian integrasi antarmuka dan REST API."*

---

### Unit 4: Mengidentifikasi Library, Komponen, atau Framework yang Digunakan (`J.620100.003.01`)
- **Konsep:** Memilih dan mendokumentasikan pustaka eksternal serta kerangka kerja (*framework*) guna mempercepat pengembangan dan menjaga standar keamanan aplikasi.
- **Implementasi pada Sistem (Periksa `composer.json` & `package.json`):**
  - **Backend Framework:** `laravel/framework: ^13.0` (Fondasi MVC, routing, Eloquent ORM, middleware, CSRF protection).
  - **API Authentication:** `laravel/sanctum: ^4.0` (Autentikasi token berbasis bearer untuk klien eksternal/mobile).
  - **PDF Export Generator:** `barryvdh/laravel-dompdf: ^3.1` (Ekspor Berita Acara dan Rekapitulasi Suara resmi ke format PDF).
  - **CSS Engine:** `@tailwindcss/vite: ^4.0` (Styling modern tanpa bloating CSS).
  - **Micro-Interactions:** `alpinejs: ^3.17` (Reaktivitas modal konfirmasi, tabulasi, dan countdown bilik suara).
  - **Visualisasi Data:** `chart.js: ^4.5` (Rendering grafik perolehan suara Quick Count).
  - **HTTP Client:** `axios: ^1.11` (Pengiriman data suara asinkron dan polling realtime).
- **Cara Menjawab Asesor:**
  > *"Pada backend saya menggunakan framework Laravel 13 yang dilengkapi Laravel Sanctum untuk keamanan API serta DomPDF untuk pencetakan dokumen legal pemilu. Di sisi frontend, saya memadukan Tailwind CSS v4 untuk tampilan, Alpine.js untuk logika interaksi, dan Chart.js untuk diagram Quick Count."*

---

### Unit 5: Menerapkan Struktur Data (`J.620100.004.02`)
- **Konsep:** Mengimplementasikan tipe data terstruktur, array, koleksi data, dan pemetaan objek secara efisien di dalam program.
- **Implementasi pada Sistem:**
  - **Array Asosiatif & Multidimensi:** Digunakan dalam kalkulasi hasil suara di controller:
    ```php
    $results = [
        'event_id' => $event->id,
        'total_votes' => $totalVotes,
        'candidates' => $event->candidates->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'votes_count' => $c->votes_count,
            'percentage' => $totalVotes > 0 ? round(($c->votes_count / $totalVotes) * 100, 2) : 0
        ])
    ];
    ```
  - **Laravel Collections:** Pemanfaatan method *higher-order* seperti `map()`, `filter()`, `pluck()`, `groupBy()`, dan `sortByDesc()` untuk memanipulasi data DPT dan hasil suara di memori server tanpa query berulang.
  - **Model Type Casting:** Pada model `app/Models/Event.php`, atribut seperti `start_time` di-cast otomatis ke tipe `datetime` (Carbon Instance) dan status boolean ke tipe `boolean`.
- **Cara Menjawab Asesor:**
  > *"Struktur data diimplementasikan melalui array multidimensi terasosiasi dan Laravel Collection pipeline. Saya juga memanfaatkan Eloquent Attribute Casting agar data tanggal dan status boolean terkonversi secara type-safe ke objek Carbon dan tipe boolean native PHP."*

---

### Unit 6: Menerapkan User Interface (`J.620100.005.02`)
- **Konsep:** Membangun antarmuka aplikasi web yang mudah dipahami, konsisten, memiliki feedback interaktif, serta mematuhi hierarki visual yang baik.
- **Implementasi pada Sistem:**
  - **Sistem Template Master:** Pemisahan layout antara halaman publik/bilik suara (`resources/views/layouts/voter.blade.php`) dengan dashboard administrator (`resources/views/layouts/admin.blade.php`).
  - **Feedback Interaktif:** Tampilan alert dinamis saat sukses mencoblos, pesan error validasi input NISN tidak valid, dan modal dialog sebelum tombol pencoblosan final diproses.
  - **Dashboard Navigasi Bersih:** Menu navigasi samping (*sidebar*) yang intuitif untuk mengelola Data Event, Kandidat, DPT, dan Hasil Perhitungan Suara.
- **Cara Menjawab Asesor:**
  > *"Antarmuka dibangun menggunakan Blade Templating Engine dengan prinsip modularitas. Antarmuka pemilih dirancang sangat sederhana agar siswa tidak kebingungan di bilik suara, sedangkan antarmuka admin menyajikan ringkasan statistik komprehensif melalui kartu metrik dan grafik interaktif."*

---

### Unit 7: Menentukan Entitas dan Relasi Data dalam Basis Data (`J.620100.007.01`)
- **Konsep:** Mengidentifikasi kebutuhan data bisnis, merancang entitas tabel, mendefinisikan primary key/foreign key, serta membangun relasi antar tabel (1:1, 1:N, N:M).
- **Implementasi pada Sistem:**
  - **7 Entitas Utama Database:**
    1. `users`: Akun panitia dan admin (PK: `id`).
    2. `events`: Agenda pemilihan aktif (PK: `id`).
    3. `candidates`: Pasangan calon ketua & wakil (PK: `id`, FK: `event_id`).
    4. `voters`: Master data DPT pemilih (PK: `id`, unik: `identifier` / NISN).
    5. `event_participations`: Status kehadiran & hak suara pemilih per agenda (PK: `id`, FK: `voter_id`, `event_id`).
    6. `votes`: Surat suara digital yang masuk (PK: `id`, FK: `event_id`, `candidate_id`). **TIDAK MENYIMPAN `voter_id`**.
    7. `audit_logs`: Jejak audit forensik aktivitas sistem (PK: `id`, FK: `user_id` opsional).
  - **Relasi Eloquent:**
    - `Event` $\rightarrow$ `hasMany(Candidate::class)` & `hasMany(Vote::class)`
    - `Candidate` $\rightarrow$ `belongsTo(Event::class)` & `hasMany(Vote::class)`
    - `Voter` $\rightarrow$ `hasMany(EventParticipation::class)`
    - `EventParticipation` $\rightarrow$ `belongsTo(Voter::class)` & `belongsTo(Event::class)`
  - **Prinsip Anonymity (Kerahasiaan Suara):** Entitas `votes` di-decouple secara fisik dari entitas `voters` untuk menjamin kerahasiaan pilihan pemilih.
- **Cara Menjawab Asesor:**
  > *"Sistem memiliki 7 entitas tabel yang dinormalisasi dengan relasi foreign key cascade. Keunggulan rancangan ini terletak pada pemisahan entitas pemilih dan surat suara (decoupling), sehingga menjamin asas LUBERJURDIL di mana data pilihan suara tidak dapat ditelusuri kembali ke identitas siswa pencoblos."*

---

### Unit 8: Menulis Kode yang Mengeksekusi Teks, Grafik, dan Elemen Multimedia (`J.620100.010.01`)
- **Konsep:** Memproses manipulasi teks, memvisualisasikan data dalam bentuk diagram/grafik, serta mengelola file multimedia (unggah, kompresi, dan penampilan gambar).
- **Implementasi pada Sistem:**
  - **Eksekusi Teks:** Parsing file CSV master DPT, manipulasi string nama dan NISN, serta format tanggal agenda pemilihan menggunakan library Carbon Indonesia (`translatedFormat('l, d F Y')`).
  - **Eksekusi Grafik:** Visualisasi hasil perhitungan suara menggunakan library JavaScript `Chart.js` pada halaman Quick Count. Grafik berbentuk *Horizontal Bar Chart* dan *Donut Chart* yang menampilkan persentase suara per paslon secara proporsional.
  - **Eksekusi Multimedia:** Modul upload foto paslon kandidat pada menu admin. File gambar divalidasi formatnya (`jpeg,png,jpg,webp`), disimpan di folder terisolasi `storage/app/public/candidates/`, dan diakses publik via symbolic link `asset('storage/...')`.
- **Cara Menjawab Asesor:**
  > *"Untuk teks, sistem memproses impor file CSV dan format tanggal resmi. Untuk grafik, sistem mengintegrasikan Chart.js yang membaca agregasi query SQL secara dinamis. Untuk multimedia, sistem menangani upload berkas foto kandidat dengan validasi MIME-type dan proteksi penyimpanan pada direktori storage publik Laravel."*

---

### Unit 9: Melakukan Instalasi Software Tools untuk Pemrograman (`J.620100.011.01`)
- **Konsep:** Menyiapkan lingkungan kerja lokal (*development environment*) melalui instalasi interpreter bahasa, package manager, dan ekstensi pendukung.
- **Implementasi pada Sistem:**
  - **Instalasi PHP & Ekstensi:** Memastikan PHP 8.3 aktif beserta ekstensi: `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `gd`, dan `curl`.
  - **Instalasi Composer:** Mengunduh dan mengonfigurasi Composer versi terbaru untuk manajemen dependensi PHP.
  - **Instalasi Node.js:** Memasang Node.js v20+ LTS beserta NPM untuk ekosistem build frontend.
  - **Instalasi Dependensi Proyek:**
    ```bash
    composer install
    npm install
    ```
- **Cara Menjawab Asesor:**
  > *"Lingkungan kerja saya siapkan dengan menginstal PHP 8.3 beserta ekstensi database SQLite dan manipulasi gambar, Composer untuk ekosistem dependensi backend, serta Node.js untuk ekosistem build frontend. Seluruh dependensi terverifikasi melalui perintah `composer install` dan `npm install`."*

---

### Unit 10: Melakukan Pengaturan Software Tools untuk Pemrograman (`J.620100.012.01`)
- **Konsep:** Mengonfigurasi parameter lingkungan, file environment, koneksi database, dan build pipeline agar sistem siap dijalankan tanpa error.
- **Implementasi pada Sistem:**
  - **Environment Configuration (`.env`):**
    - `APP_NAME="School E-Voting"`
    - `APP_ENV=local` / `APP_DEBUG=true`
    - `APP_KEY=base64:...` (digenerate via `php artisan key:generate`)
    - `DB_CONNECTION=sqlite` (menunjuk ke `database/database.sqlite`)
  - **Pengaturan Multi-Guard Auth (`config/auth.php`):**
    - Konfigurasi guard `web` dengan driver session menggunakan provider tabel `users`.
    - Konfigurasi guard `voter` dengan driver session menggunakan provider tabel `voters`.
  - **Pengaturan Build Pipeline (`vite.config.js`):**
    - Konfigurasi plugin `@tailwindcss/vite` dan `laravel-vite-plugin` untuk kompilasi berkas `resources/css/app.css` dan `resources/js/app.js`.
- **Cara Menjawab Asesor:**
  > *"Pengaturan sistem dipusatkan pada file `.env` untuk parameter lingkungan dan basis data SQLite. Di samping itu, file `config/auth.php` dikonfigurasi secara khusus untuk memisahkan guard autentikasi admin dan pemilih, serta file `vite.config.js` dikonfigurasi untuk hot-module replacement (HMR) saat styling Tailwind dimodifikasi."*

---

## 4. PEMBAHASAN MENDALAM UNIT 11: PENGGUNAAN KEMBALI SUBRUTIN (*REUSABILITY*)
> **Unit Kompetensi:** `J.620100.014.01` — Menerapkan Penggunaan Kembali Subrutin-subrutin  
> **Standar Penilaian:** Asesor mewajibkan pembuktian bahwa kode **TIDAK MENDUPLIKASI LOGIKA (*DRY Principle*)**, subrutin dapat menerima parameter, mengembalikan nilai, dan **dipanggil dari beberapa tempat berbeda (misal Web Controller dan REST API Controller)**.

Di bawah ini adalah pemetaan lokasi file, baris kode, dan implementasi konkret subrutin yang telah diterapkan pada sistem E-Voting:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                               ARSITEKTUR SUBRUTIN REUSABLE                             │
└───────────────────────────────────────────┬────────────────────────────────────────────┘
                                            │
        ┌───────────────────────────────────┼───────────────────────────────────┐
        ▼                                   ▼                                   ▼
 [ACTION CLASS]                      [SERVICE CLASS]                     [MODEL HELPER]
 app/Actions/CastVoteAction.php      app/Services/VoterImportService.php app/Models/AuditLog.php
        │                                   │                                   │
   Dipanggil Oleh:                     Dipanggil Oleh:                     Dipanggil Oleh:
   1. Web VotingController             1. Web VoterManagementController    1. Auth Controllers
   2. API VotingController             2. API VoterController              2. Action & Service
        │                                   │                                   │
        ▼                                   ▼                                   ▼
 [Pessimistic Locking & ACID]         [CSV Parsing & Validation]           [Audit Logging Trail]
```

---

### 4.1 Subrutin Utama: Action Class `CastVoteAction` (Pencoblosan Suara)

Subrutin ini adalah **jantung logika bisnis aplikasi**. Dibuat sebagai *Action Class* mandiri agar logika penguncian transaksi dan pencatatan suara tidak ditulis ulang di dua tempat berbeda (Web dan API).

#### A. Lokasi File & Penjelasan Kode
- **Lokasi File:** [`app/Actions/CastVoteAction.php`](/c:/Users/USER/evoting/app/Actions/CastVoteAction.php)
- **Rentang Baris:** Baris 1 s.d. 72
- **Nama Method Subrutin:** `public function execute(Event $event, Voter $voter, Candidate $candidate): Vote`

```php
namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CastVoteAction
{
    /**
     * Mengeksekusi pencoblosan suara secara atomik dan thread-safe
     */
    public function execute(Event $event, Voter $voter, Candidate $candidate): Vote
    {
        // 1. Validasi integritas kandidat
        if ($candidate->event_id !== $event->id) {
            throw ValidationException::withMessages([
                'candidate_id' => ['Kandidat yang dipilih tidak terdaftar pada agenda pemilihan ini.'],
            ]);
        }

        // 2. Eksekusi database transaction atomik
        return DB::transaction(function () use ($event, $voter, $candidate) {
            // Penguncian baris pesimis (Pessimistic Row Locking)
            $participation = EventParticipation::where('event_id', $event->id)
                ->where('voter_id', $voter->id)
                ->lockForUpdate()
                ->first();

            // Verifikasi status hak pilih
            if ($participation && $participation->has_voted) {
                throw ValidationException::withMessages([
                    'vote' => ['Anda telah menggunakan hak suara Anda untuk agenda pemilihan ini.'],
                ]);
            }

            // Catat suara tanpa menyimpan voter_id (Vote Anonymity)
            $vote = Vote::create([
                'event_id' => $event->id,
                'candidate_id' => $candidate->id,
                'voted_at' => now(),
            ]);

            // Perbarui status partisipasi pemilih
            EventParticipation::updateOrCreate(
                ['event_id' => $event->id, 'voter_id' => $voter->id],
                ['has_voted' => true, 'voted_at' => now()]
            );

            // Logging audit keamanan
            AuditLog::log('VOTE_CAST', "Hak suara digunakan pada agenda: {$event->title}");

            return $vote;
        });
    }
}
```

#### B. Bukti Pemanggilan Ganda (*Proof of Reusability*)

##### Tempat Pemanggilan 1: Web Controller (Antarmuka Browser Siswa)
- **Lokasi File:** [`app/Http/Controllers/Voter/VotingController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Voter/VotingController.php:154)
- **Baris Pemanggilan:** Baris 154
- **Konteks:** Dipanggil di dalam method `submitVote(Request $request, Event $event, CastVoteAction $castVoteAction)` saat siswa menekan tombol konfirmasi coblos di browser.

```php
// app/Http/Controllers/Voter/VotingController.php (Baris 154)
$castVoteAction->execute($event, $voter, $candidate);

return redirect()->route('voter.voting.success', $event)
    ->with('success', 'Suara Anda berhasil dicatat.');
```

##### Tempat Pemanggilan 2: RESTful API Controller (Aplikasi Mobile / Klien Eksternal)
- **Lokasi File:** [`app/Http/Controllers/Api/Voter/VotingController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Api/Voter/VotingController.php:160)
- **Baris Pemanggilan:** Baris 160
- **Konteks:** Dipanggil di dalam method `castVote(Request $request, Event $event, CastVoteAction $castVoteAction)` saat request datang melalui protokol HTTP POST JSON.

```php
// app/Http/Controllers/Api/Voter/VotingController.php (Baris 160)
$vote = $castVoteAction->execute($event, $voter, $candidate);

return response()->json([
    'message' => 'Suara berhasil dicatat',
    'data' => [
        'event' => $event->title,
        'voted_at' => $vote->voted_at,
    ],
]);
```

**Kesimpulan untuk Asesor:**  
Jika ada perubahan aturan transaksi pencoblosan di masa depan (misal: menambahkan hashing kriptografi pada surat suara), perubahan **cukup dilakukan satu kali di `CastVoteAction.php`**. Baik Web maupun API otomatis mendapatkan pembaruan tersebut tanpa duplikasi kode.

---

### 4.2 Subrutin Pendukung: Service Class `VoterImportService` (Impor Data DPT)

Subrutin ini menangani penguraian (*parsing*), validasi data baris per baris, dan penyimpanan massal daftar pemilih dari format CSV.

#### A. Lokasi File & Method
- **Lokasi File:** [`app/Services/VoterImportService.php`](/c:/Users/USER/evoting/app/Services/VoterImportService.php)
- **Method 1 (Web):** `public function importCsvForWeb($file): array`
- **Method 2 (API CSV):** `public function importCsvForApi($file): array`
- **Method 3 (API JSON Array):** `public function importArrayForApi(array $votersData): array`

#### B. Bukti Pemanggilan Ganda
1. **Web Controller:**  
   - Lokasi: [`app/Http/Controllers/Admin/VoterManagementController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Admin/VoterManagementController.php:154) (Baris 154)  
   - Pemanggilan: `$result = $importService->importCsvForWeb($request->file('csv_file'));`
2. **REST API Controller:**  
   - Lokasi: [`app/Http/Controllers/Api/Admin/VoterController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Api/Admin/VoterController.php:67) (Baris 67 & 84)  
   - Pemanggilan: `$result = $importService->importCsvForApi($request->file('file'));` dan `$result = $importService->importArrayForApi($validated['voters']);`

---

### 4.3 Subrutin Helper: Static Method `AuditLog::log()` (Pencatatan Audit Trail)

Subrutin statis ini digunakan di seluruh lapisan controller dan action untuk mencatat setiap aktivitas penting ke dalam database secara seragam.

- **Lokasi File:** [`app/Models/AuditLog.php`](/c:/Users/USER/evoting/app/Models/AuditLog.php:78)
- **Rentang Baris:** Baris 78 s.d. 112
- **Definisi Subrutin:**
  ```php
  public static function log(string $action, string $description, ?int $userId = null): self
  {
      return self::create([
          'user_id' => $userId ?? auth('web')->id(),
          'action' => $action,
          'description' => $description,
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
      ]);
  }
  ```
- **Contoh Pemanggilan Reusable di Berbagai Tempat:**
  - Login Admin: `AuditLog::log('LOGIN', 'User masuk ke sistem');`
  - Tambah DPT: `AuditLog::log('VOTER_CREATED', "Menambahkan DPT: {$voter->nama}");`
  - Selesai Coblos: `AuditLog::log('VOTE_CAST', "Hak suara digunakan pada agenda: {$event->title}");`

---

### 4.4 Subrutin Tampilan: Reusable Blade Components (Frontend)

Prinsip *reusability* tidak hanya berlaku pada backend PHP, tetapi juga pada elemen antarmuka (UI). Komponen Blade diekstrak menjadi berkas parsial yang dapat dipanggil berulang kali menggunakan tag `<x-... />`:

1. **Komponen Kartu Paslon (`resources/views/components/candidate-card.blade.php`):**  
   Menampilkan nomor urut, foto, nama calon, visi-misi, serta tombol coblos. Dipanggil di bilik suara pemilih dan di halaman pratinjau admin.
2. **Komponen Modal Dialog (`resources/views/components/modal.blade.php`):**  
   Menyediakan kotak dialog konfirmasi pop-up yang reusable untuk konfirmasi pencoblosan suara maupun konfirmasi hapus data DPT.
3. **Komponen Alert & Badge:**  
   - `resources/views/components/alert.blade.php`: Menampilkan notifikasi sukses/error.
   - `resources/views/components/badge.blade.php`: Menampilkan label status (Aktif, Selesai, Ditutup).
   - `resources/views/components/button.blade.php`: Standarisasi tombol primer, sekunder, dan bahaya.

---

## 5. Panduan Menjawab Pertanyaan Kritis Asesor (Tanya Jawab Langsung)

Berikut adalah simulasi pertanyaan kritis yang sering diajukan asesor LSP P3 beserta kunci jawaban berbasis bukti kode:

### Pertanyaan 1: "Bagaimana cara Anda menjamin sistem tidak terjadi pencoblosan ganda (double-voting) jika pemilih menekan tombol coblos berkali-kali secara cepat?"
- **Kunci Jawaban:**
  > *"Sistem kami menjamin zero double-voting melalui dua lapis proteksi:*  
  > 1. *Di sisi frontend, tombol coblos langsung disable saat diklik pertama kali menggunakan Alpine.js.*  
  > 2. *Di sisi backend, subrutin `CastVoteAction` menggunakan transaksi database atomik `DB::transaction()` yang dipadukan dengan **Pessimistic Row Locking** (`lockForUpdate()`) pada tabel `event_participations`. Ketika request pertama masuk, baris data pemilih dikunci di level database engine. Request kedua yang tiba bersamaan akan menunggu hingga transaksi pertama selesai dan langsung ditolak karena status `has_voted` sudah bernilai true."*

### Pertanyaan 2: "Mengapa Anda memisahkan `CastVoteAction` dari Controller? Mengapa tidak ditaruh langsung di Controller saja?"
- **Kunci Jawaban:**
  > *"Itu adalah penerapan Unit Kompetensi 11 tentang Reusability dan prinsip Clean Code (Single Responsibility Principle). Sistem kami memiliki dua jalur akses pencoblosan: melalui Web Browser (Blade) dan melalui REST API untuk aplikasi mobile. Jika logika transaksi diletakkan di Controller, saya terpaksa menduplikasi 40 baris kode yang sama di dua controller berbeda. Dengan mengekstraknya ke Action Class `CastVoteAction`, kode menjadi DRY, mudah diuji secara modular, dan pemeliharaannya terpusat di satu file."*

### Pertanyaan 3: "Bagaimana sistem Anda membuktikan bahwa suara pemilih benar-benar rahasia (asas Rahasia)?"
- **Kunci Jawaban:**
  > *"Kerahasiaan dijamin secara struktural melalui pemisahan fisik entitas (*decoupling*) pada basis data. Tabel surat suara (`votes`) hanya memiliki kolom `event_id`, `candidate_id`, dan `voted_at`, **sama sekali tidak memiliki kolom `voter_id`**. Sedangkan data siapa yang telah hadir mencoblos dicatat di tabel terpisah (`event_participations`). Dengan demikian, bahkan database administrator atau super admin yang memiliki akses langsung ke SQLite tidak dapat menghubungkan siapa pemilih yang mencoblos paslon tertentu."*

---

## 6. Checklist Persiapan Teknis Sebelum Masuk Ruang Asesmen

Sebelum asesor memanggil Anda untuk demonstrasi sistem, pastikan poin-poin berikut siap di laptop:
- [ ] Buka project di **Visual Studio Code** dan pastikan terminal tidak sedang error.
- [ ] Buka file [`app/Actions/CastVoteAction.php`](/c:/Users/USER/evoting/app/Actions/CastVoteAction.php) pada split tab kanan untuk ditunjukkan langsung saat Unit 11 ditanyakan.
- [ ] Buka file [`app/Http/Controllers/Voter/VotingController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Voter/VotingController.php:154) dan [`app/Http/Controllers/Api/Voter/VotingController.php`](/c:/Users/USER/evoting/app/Http/Controllers/Api/Voter/VotingController.php:160) pada tab lain untuk membuktikan pemanggilan ganda.
- [ ] Pastikan web server lokal berjalan:
  ```bash
  php artisan serve
  npm run dev
  ```
- [ ] Buka browser pada tab:
  1. Halaman Login / Dashboard Admin (`http://127.0.0.1:8000/admin/login`).
  2. Halaman Bilik Suara Siswa (`http://127.0.0.1:8000/voter/login`).
  3. Halaman Quick Count Perolehan Suara Realtime.
- [ ] Siapkan dokumen laporan Bab 1 s.d. Bab 4 dan diagram Draw.io yang telah dirancang.
