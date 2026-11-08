# BAB II: ANALISIS KEBUTUHAN SISTEM DAN SPESIFIKASI PROGRAM

Bab ini menguraikan analisis kebutuhan sistem secara mendalam untuk aplikasi *E-Voting* berbasis web pada pemilihan ketua OSIS maupun pemilihan umum lingkungan sekolah. Analisis ini disusun berdasarkan standar Unit Kompetensi SKKNI Pemrogram Junior (**J.620100.009.01 - Menggunakan Spesifikasi Program**), yang mencakup identifikasi pengguna beserta hak aksesnya, pemodelan kebutuhan fungsional perangkat lunak, penetapan parameter kualitas non-fungsional, serta penentuan spesifikasi lingkungan operasional sistem.

---

## 2.1 Analisis Aktor dan Hak Akses Pengguna

Dalam arsitektur perangkat lunak e-voting ini, pengguna dikelompokkan ke dalam 3 (tiga) aktor utama dengan pemisahan wewenang (*separation of concerns*) yang tegas. Pemisahan wewenang ini diimplementasikan menggunakan arsitektur *Role-Based Access Control* (RBAC) pada panel administratif internal serta pemisahan mekanisme autentikasi *Multi-Guard* bawaan framework Laravel.

### 2.1.1 Identifikasi dan Deskripsi Aktor

1. **Super Admin (Administrator Global Sistem)**
   - Merupakan pengguna dengan tingkat hak akses tertinggi (`role = 'SUPER_ADMIN'`) pada model `App\Models\User` yang bertindak sebagai pengelola sistem secara menyeluruh.
   - **Manajemen Pengguna Internal (`/admin/users`):** Berwenang penuh melakukan penambahan (*create*), pembaruan data (*update*), penetapan peran (`SUPER_ADMIN` atau `PETUGAS`), serta penonaktifan akun staf internal.
   - **Manajemen Master DPT Global (`/admin/voters`):** Mengelola basis data induk pemilih terpusat lintas agenda (siswa dan guru), mencakup penambahan manual, pembaruan identitas, filterisasi berdasarkan tipe/kelas, pengalihan status aktif (`is_active`), ekspor data DPT, serta impor massal melalui berkas CSV yang dilengkapi sanitasi UTF-8 BOM dan validasi keunikan nomor induk (`identifier`).
   - **Pemantauan Audit Log & Pemeliharaan Sistem (`/admin/logs`):** Memantau riwayat rekaman jejak audit sistem (aktivitas login staf, kegagalan autentikasi, impor DPT, transaksi voting, perubahan status pemilihan), mengosongkan berkas log, serta melakukan pembersihan cache aplikasi (*application cache clearing*).
   - **Hak Kelola Universal Agenda Pemilihan (`/admin/events`):** Memiliki hak istimewa (*super privilege / bypass*) untuk meninjau, mengedit, mempublikasikan, menutup, maupun menghapus seluruh agenda pemilihan yang ada di dalam sistem tanpa terikat kepemilikan agenda.

2. **Petugas (Panitia Penyelenggara Agenda Pemilihan)**
   - Merupakan staf internal dengan peran `role = 'PETUGAS'` pada model `App\Models\User` yang bertindak sebagai panitia pelaksana agenda pemilihan (misalnya panitia pemilihan ketua OSIS, MPK, atau ekstrakurikuler).
   - **Manajemen Agenda Pemilihan Mandiri:** Berhak membuat agenda pemilihan baru (`/admin/events`), menentukan judul, deskripsi, jendela waktu operasional pemungutan suara (`start_time` dan `end_time`), batasan kriteria hak pilih (`target_scope`: `ALL`, `GURU`, `SISWA`, atau `KELAS`), serta penargetan kelas spesifik (`target_class`).
   - **Kolaborasi Kepanitiaan Multi-Event:** Mengundang petugas lain sebagai kolaborator panitia melalui tautan/token undangan (`invite_token`), atau bergabung ke agenda pemilihan milik petugas lain menggunakan token undangan yang valid (disimpan pada tabel relasi pivot `event_officers`).
   - **Manajemen Pasangan Calon / Kandidat:** Mengelola data kandidat paslon (`/admin/events/{event}/candidates`), meliputi nomor urut, nama ketua, nama wakil, foto profil/poster, visi, misi, dan program kerja unggulan pada agenda yang berada di bawah wewenangnya.
   - **Pengendalian Siklus Hidup Agenda:** Mengatur status siklus hidup agenda (`DRAFT` -> `PUBLISHED` -> `CLOSED`), melakukan regenerasi kode bilik suara (`room_code`), serta meregenerasi token undangan kepanitiaan.
   - **Pemantauan Suara Real-time (*Live Monitoring*):** Memantau perolehan suara secara langsung melalui antarmuka grafik dinamis dan memantau persentase partisipasi pemilih pada agenda kelolaannya.
   - **Ekspor Dokumen Berita Acara & Hasil:** Mengunduh Berita Acara Rekapitulasi Hasil Pemilihan resmi dalam format PDF (menggunakan pustaka *DomPDF*) serta mengunduh rincian rekapitulasi perolehan suara dalam format CSV (dengan dukungan UTF-8 BOM).
   - **Batasan Kewenangan (*Restriksi*):** Petugas dilarang mengakses menu Kelola Pengguna, Master DPT, dan Audit Log sistem (dicegat oleh middleware `role:SUPER_ADMIN` dengan respon HTTP 403 *Forbidden*). Petugas juga dibatasi hanya dapat mengelola agenda yang ia buat sendiri (`created_by_user_id`) atau agenda di mana ia terdaftar sebagai panitia kolaborator.

3. **Pemilih (Voter / Siswa / Guru)**
   - Merupakan civitas sekolah (Siswa atau Guru) yang terdaftar dalam master DPT (`voters`) dengan status aktif (`is_active = true`).
   - **Karakteristik Tipe Pemilih:**
     * `SISWA`: Teridentifikasi melalui Nomor Induk Siswa Nasional (NISN) pada atribut `identifier` dan wajib memiliki atribut penempatan rombongan belajar pada kolom `kelas` (contoh: "XII RPL 1").
     * `GURU`: Teridentifikasi melalui Nomor Induk Pegawai (NIP/NIK/Kode Guru) pada atribut `identifier` dengan atribut `kelas` bernilai kosong (`null`).
   - **Akses Bilik Suara Digital (`/bilik-suara/{roomCode}`):**
     * Memasuki bilik suara digital menggunakan kombinasi kredensial `identifier` (NISN/NIP) dan `room_code` (kode bilik unik agenda yang dipublikasikan oleh panitia, contoh: "OSIS2026").
     * Melewati validasi kelayakan memilih (*eligibility check*) yang dieksekusi secara otomatis oleh sistem, mencakup status keaktifan pemilih, status keterbukaan agenda (`PUBLISHED`), jendela waktu pemungutan suara (`start_time` s.d. `end_time`), kesesuaian target pemilih (`target_scope`), serta status pencoblosan pada tabel `event_participations`.
     * Meninjau profil kandidat (foto, visi, dan misi), menentukan 1 (satu) kandidat pilihan, mengonfirmasi pilihan melalui dialog verifikasi, dan mengeksekusi pencoblosan suara secara anonim.
     * Sesi pemilih langsung dihanguskan (*session invalidate & logout*) secara otomatis detik itu juga setelah pencoblosan berhasil dicatat, mencegah penyalahgunaan sesi pada perangkat bilik suara bersama.

---

### 2.1.2 Matriks Hak Akses (Role-Permission Matrix)

Matriks hak akses berikut mendefinisikan batas kewenangan operasional setiap aktor terhadap seluruh modul dan fungsionalitas sistem berdasarkan implementasi rute serta middleware pengaman:

| Modul & Fungsionalitas Sistem | Super Admin | Petugas (Panitia) | Pemilih (Voter) | Keterangan & Proteksi Teknis |
| :--- | :---: | :---: | :---: | :--- |
| **Autentikasi Internal Staf (`/admin/login`)** | Hak Penuh | Hak Penuh | Ditolak | Guard `web` (tabel `users`), hashing `Bcrypt` |
| **Autentikasi Pemilih Bilik Suara (`/bilik-suara/login`)** | Ditolak | Ditolak | Hak Penuh | Guard `voter` (tabel `voters`), input NISN/NIP & Room Code |
| **Manajemen Pengguna Staf Internal (`/admin/users`)** | CRUD | Ditolak (403) | Ditolak | Dilindungi middleware `role:SUPER_ADMIN` |
| **Manajemen Master DPT Global (`/admin/voters`)** | CRUD | Ditolak (403) | Ditolak | Dilindungi middleware `role:SUPER_ADMIN` |
| **Impor Massal & Ekspor CSV DPT** | Hak Penuh | Ditolak (403) | Ditolak | Validasi struktur CSV, sanitasi BOM, verifikasi duplikasi |
| **Audit Logs Sistem & Pembersihan Cache (`/admin/logs`)** | Hak Penuh | Ditolak (403) | Ditolak | Dilindungi middleware `role:SUPER_ADMIN`, artisan optimize:clear |
| **Pembuatan Agenda Pemilihan Baru (`POST /admin/events`)** | Hak Penuh | Hak Penuh | Ditolak | Menetapkan judul, jadwal, scope pemilih, dan room code |
| **Kelola Agenda Sendiri / Kolaborasi (`/admin/events/{id}`)** | Hak Penuh | Hak Penuh | Ditolak | Validasi wewenang via method `canBeManagedBy($user)` |
| **Kelola Agenda Milik Petugas Lain** | Hak Penuh (Bypass) | Ditolak (403) | Ditolak | Hanya diizinkan jika petugas bergabung via `invite_token` |
| **Manajemen Paslon Kandidat Agenda** | CRUD | CRUD (Agenda Sendiri) | Ditolak | Pengelolaan nomor urut, foto ketua/wakil, dan visi-misi |
| **Kontrol Status Agenda & Regenerasi Kode Bilik** | Hak Penuh | Hak Penuh (Agenda Sendiri) | Ditolak | Transisi status DRAFT -> PUBLISHED -> CLOSED |
| **Pemantauan Perolehan Suara Real-time (*Live Monitoring*)** | Hak Penuh (Semua) | Hak Penuh (Agenda Sendiri) | Ditolak | Dasbor pemantauan suara masuk dan persentase partisipasi |
| **Ekspor Berita Acara Rekapitulasi (PDF & CSV)** | Hak Penuh (Semua) | Hak Penuh (Agenda Sendiri) | Ditolak | Berita Acara resmi PDF (DomPDF) dan data rekapitulasi CSV |
| **Akses Antarmuka Bilik Suara (`/bilik-suara/{roomCode}`)** | Ditolak | Ditolak | Hak Penuh | Dilindungi middleware `auth:voter`, verifikasi eligibility |
| **Eksekusi Pencoblosan Suara Sah (Voting)** | Ditolak | Ditolak | Hak Penuh (1 Kali) | Transaksi atomik database, penjaminan anonimitas mutlak |

---

### 2.1.3 Mekanisme Autentikasi Multi-Guard & Proteksi Sesi

Untuk mengisolasi hak akses secara struktural dan mencegah eskalasi wewenang (*privilege escalation*), arsitektur sistem memisahkan mekanisme autentikasi dan manajemen sesi secara fisik menggunakan konfigurasi *Multi-Guard Authentication* bawaan framework Laravel:

1. **Guard `web` (Pengguna Administratif Internal):**
   - **Basis Entitas:** Menggunakan provider Eloquent model `App\Models\User` yang terhubung langsung ke tabel `users`.
   - **Kredensial Autentikasi:** Menggunakan alamat surat elektronik (`email`) dan kata sandi rahasia (*password*).
   - **Standar Enkripsi:** Kata sandi disimpan menggunakan fungsi hash satu arah algoritma `Bcrypt` dengan *work factor* standar industri.
   - **Proteksi Sesi:** Dilindungi dengan token pencegahan pemalsuan permintaan lintas situs (*Cross-Site Request Forgery* / CSRF) pada setiap mutasi data, regenerasi ID sesi saat berhasil masuk (`$request->session()->regenerate()`) untuk mencegah serangan *session fixation*, serta pencatatan audit log saat terjadi keberhasilan maupun kegagalan login.
   - **Lapisan Otorisasi:** Akses rute administratif diproteksi oleh middleware `auth:web`, dilanjutkan dengan middleware `role:SUPER_ADMIN` untuk modul sensitif (users, voters, logs), serta method wewenang `canBeManagedBy($user)` untuk operasi pada agenda pemilihan.

2. **Guard `voter` (Pemilih Bilik Suara Digital):**
   - **Basis Entitas:** Menggunakan provider Eloquent model `App\Models\Voter` yang terhubung langsung ke tabel `voters`.
   - **Kredensial Autentikasi:** Masuk ke bilik suara menggunakan kombinasi `identifier` (NISN/NIP) yang terdaftar aktif di master DPT dan `room_code` agenda pemilihan yang valid. Pemilih tidak memiliki kata sandi mandiri di basis data untuk meminimalkan friksi operasional di lingkungan sekolah.
   - **Validasi Hak Pilih (*Pre-Authentication Checks*):** Sebelum sesi bilik suara diterbitkan, kontroler `VoterAuthController` mengeksekusi serangkaian validasi berurutan:
     1. Memastikan akun pemilih berstatus aktif (`is_active = true`).
     2. Memastikan agenda berstatus dipublikasikan (`status = 'PUBLISHED'`) dan waktu saat ini berada di dalam rentang waktu yang diizinkan (`start_time <= now() <= end_time`).
     3. Memastikan pemilih memenuhi segmentasi hak pilih agenda (`target_scope`: `ALL`, `GURU`, `SISWA`, atau `KELAS`).
     4. Memastikan pemilih belum pernah menggunakan hak pilihnya pada agenda tersebut melalui pengecekan relasi `event_participations` (`has_voted == false`).
   - **Proteksi Sesi Sementara (*Transient Session*):** Jika seluruh validasi terpenuhi, sesi diaktifkan melalui `Auth::guard('voter')->login($voter)` dan menyimpan kunci bilik aktif `session(['voter_room_code' => $event->room_code])`.
   - **Penghancuran Sesi Instan (*Auto-Termination*):** Sesi bilik suara bersifat sementara. Segera setelah surat suara berhasil dicatat ke basis data melalui aksi `CastVoteAction`, sistem secara otomatis memanggil `Auth::guard('voter')->logout()`, menghanguskan sesi (`$request->session()->invalidate()`), serta meregenerasi token CSRF (`$request->session()->regenerateToken()`). Mekanisme ini menjamin tidak ada sesi yang tertinggal ketika perangkat komputer/tablet bilik suara digunakan oleh pemilih berikutnya.

3. **Pemisahan Data & Penjaminan Prinsip Kerahasiaan Suara (*Secret Ballot / LUBER*):**
   - Sistem memisahkan secara tegas antara pencatatan **partisipasi pemilih** dan pencatatan **pilihan surat suara**:
     * **Tabel `event_participations`:** Mencatat identitas pemilih yang telah hadir menggunakan hak suaranya (`event_id`, `voter_id`, `has_voted = true`, `voted_at`). Tabel ini berfungsi eksklusif sebagai pencegah pencoblosan ganda (*anti-double voting*).
     * **Tabel `votes`:** Berfungsi sebagai kotak suara digital yang hanya menyimpan informasi pilihan kandidat (`event_id`, `candidate_id`, `voted_at`).
   - **Anonimitas Mutlak:** Tabel `votes` **sama sekali tidak memiliki kolom `voter_id`**. Tidak terdapat relasi kunci asing (*foreign key*) maupun jejak pengait yang menghubungkan surat suara dengan identitas pemilih tertentu. Dengan demikian, kerahasiaan pilihan pemilih terjamin 100% dan tidak dapat dilacak kembali oleh siapa pun, termasuk oleh Super Admin maupun pengembang aplikasi.
   - **Integritas Konkurensi Transaksi:** Operasi pencoblosan suara dieksekusi di dalam transaksi basis data atomik (`DB::transaction`) yang dilengkapi penguncian data secara pesimis (*pessimistic locking* via `lockForUpdate`) pada baris partisipasi pemilih, mencegah eksploitasi balapan proses (*race condition*) apabila terjadi upaya pemungutan suara simultan.

---

## 2.2 Kebutuhan Fungsional Sistem (Functional Requirements)

Kebutuhan fungsional mendefinisikan kapabilitas, proses komputasi, transformasi data, dan batasan operasional yang diimplementasikan secara konkret pada sistem e-voting. Setiap butir kebutuhan diberi kodefikasi standar **FR-xx** untuk menjamin keterlacakan (*traceability*) terhadap arsitektur kode sumber, relasi basis data, dan skenario pengujian.

### 2.2.1 Matriks Ringkas Kebutuhan Fungsional

| Kode | Nama Modul / Kebutuhan Fungsional | Aktor Utama | Deskripsi Singkat Output |
| :---: | :--- | :--- | :--- |
| **FR-01** | Autentikasi Pengguna & Pemisahan Multi-Guard | Semua Aktor | Sesi terisolasi sesuai guard `web` (staf/admin) atau `voter` (pemilih). |
| **FR-02** | Manajemen Agenda Pemilihan Multi-Event & Status Dinamis | Super Admin, Panitia | Agenda pemilihan terbit dengan kode bilik unik, rentang waktu, dan segmentasi target. |
| **FR-03** | Manajemen Pasangan Calon (Paslon) & Visi-Misi | Super Admin, Panitia | Data kandidat tersimpan dengan nomor urut berurutan otomatis, nama ketua/wakil, dan foto resmi. |
| **FR-04** | Manajemen Master DPT & Impor/Ekspor CSV | Super Admin | Master identitas pemilih (NISN/NIP) terkelola secara batch via CSV ber-BOM UTF-8. |
| **FR-05** | Kolaborasi Kepanitiaan Berbasis Token Undangan | Super Admin, Panitia | Tautan undangan token unik untuk menambahkan panitia pengelola pada agenda tertentu. |
| **FR-06** | Akses Masuk Bilik Suara Mandiri & Penegakan Bilik | Pemilih | Akses bilik suara aman berbasis identitas resmi dan kode unik bilik (*room code*). |
| **FR-07** | Mesin Validasi Hak Suara (*Eligibility Engine*) | Pemilih, Sistem | Verifikasi kelayakan hak suara multi-parameter (status akun, jendela waktu, segmentasi, status memilih). |
| **FR-08** | Eksekusi Pencoblosan Suara Anonim & Atomik | Pemilih | Surat suara digital masuk ke kotak suara tanpa relasi ke identitas pemilih dalam transaksi ACID. |
| **FR-09** | Pencegahan Suara Ganda & Kendali Konkurensi | Sistem (Otomatis) | Penguncian baris partisipasi (*pessimistic row locking*) untuk mencegah duplikasi suara secara simultan. |
| **FR-10** | Pemantauan Perolehan Suara Real-time (*Live Monitoring*) | Super Admin, Panitia | Dashboard statistik perolehan suara, persentase partisipasi DPT, dan perolehan suara per paslon secara live. |
| **FR-11** | Ekspor Berita Acara Resmi PDF Ber-Hash & Data CSV | Super Admin, Panitia | Dokumen Berita Acara PDF tervalidasi hash kriptografis SHA-256 dan berkas tabular CSV hasil rekapitulasi. |
| **FR-12** | Audit Trail Terperinci & Pemeliharaan Sistem | Super Admin | Log aktivitas sistem tersimpan terstruktur dengan fitur *pruning* berkala dan utilitas *clear cache*. |

---

### 2.2.2 Rincian Spesifikasi Kebutuhan Fungsional

Berikut adalah spesifikasi detail untuk masing-masing kebutuhan fungsional sistem:

#### FR-01: Autentikasi Pengguna & Pemisahan Multi-Guard
- **Aktor:** Super Admin, Panitia, Pemilih.
- **Masukan (Input):**
  - Staf Manajemen (Super Admin/Panitia): Alamat email resmi dan kata sandi (*password*).
  - Pemilih (Siswa/Guru): Nomor Identitas resmi (NISN untuk siswa atau NIP untuk guru).
- **Proses:**
  1. Sistem mengidentifikasi portal login yang diakses (`/login` untuk staf manajemen internal atau `/voter/login` untuk pemilih).
  2. Untuk staf internal, sistem memvalidasi kredensial email dan kata sandi terenkripsi bcrypt pada tabel `users` menggunakan guard `web`.
  3. Untuk pemilih, sistem memvalidasi keberadaan nomor identitas pada tabel `voters` menggunakan guard `voter` serta memeriksa status aktif akun (`is_active == true`).
  4. Bila kredensial sah, sistem meregenerasi Session ID untuk mengantisipasi eksploitasi *session fixation*.
- **Keluaran (Output):** 
  - Token sesi aktif dan pengalihan (*redirect*) staf internal ke dashboard administrasi (`/admin/dashboard` atau agenda terkait).
  - Sesi pemilih terotentikasi dan dialihkan ke gerbang pemilihan bilik suara (`/voter/room`).
- **Validasi & Penolakan:**
  - Penolakan login staf jika kombinasi email/password salah atau akun tidak ditemukan (*rate limiting* berlaku untuk menangkal serangan *brute-force*).
  - Penolakan login pemilih jika nomor identitas tidak terdaftar di basis data master DPT atau status akun pemilih berstatus nonaktif (`is_active == false`).

#### FR-02: Manajemen Agenda Pemilihan Multi-Event & Status Dinamis
- **Aktor:** Super Admin, Panitia.
- **Masukan (Input):** Judul agenda (`title`), deskripsi (`description`), waktu mulai (`start_time`), waktu selesai (`end_time`), batasan sasaran pemilih (`target_scope`: `ALL`, `GURU`, `SISWA`, `KELAS`), target kelas spesifik (`target_class`), dan status awal agenda (`DRAFT` atau `PUBLISHED`).
- **Proses:**
  1. Sistem melakukan validasi input formulir pembuatan agenda pemilihan.
  2. Sistem secara otomatis men-generate kode unik bilik suara (`room_code`) sepanjang 6 karakter alfanumerik acak (*uppercase*) yang unik di tabel `events`.
  3. Sistem menyimpan data agenda baru ke tabel `events`.
  4. Pada saat agenda diakses atau dieksekusi, sistem menjalankan method model `syncDynamicStatus()`:
     - Jika status masih `DRAFT`, status tetap dipertahankan.
     - Jika waktu sekarang melampaui `end_time`, status secara dinamis ditransisikan menjadi `CLOSED`.
     - Jika waktu sekarang berada di antara `start_time` dan `end_time` serta agenda memiliki minimal 2 pasangan calon terdaftar, status agenda dinyatakan aktif (`PUBLISHED`).
- **Keluaran (Output):** Agenda pemilihan tersimpan, kode bilik suara (`room_code`) aktif, dan status agenda tersinkronisasi otomatis.
- **Validasi & Penolakan:**
  - Penolakan publikasi agenda jika waktu selesai (`end_time`) mendahului atau sama dengan waktu mulai (`start_time`).
  - Penolakan pengaktifan agenda (`PUBLISHED`) jika jumlah pasangan calon yang terdaftar kurang dari 2 paslon (syarat kontestasi demokratis).
  - Penolakan perubahan jadwal jika agenda pemilihan telah dinyatakan `CLOSED` (selesai).

#### FR-03: Manajemen Pasangan Calon (Paslon) & Visi-Misi
- **Aktor:** Super Admin, Panitia.
- **Masukan (Input):** Nama calon ketua (`chairman_name`), nama calon wakil ketua (`vice_chairman_name`), visi, misi, nomor urut kandidat (`candidate_number`), serta berkas unggahan foto profil resmi kandidat (`photo`).
- **Proses:**
  1. Sistem menerima input data kandidat yang terikat pada agenda pemilihan tertentu (`event_id`).
  2. Sistem melakukan penomoran urut otomatis (*auto-increment* nomor urut paslon berikutnya) jika nomor urut tidak diisi manual oleh panitia.
  3. Sistem memvalidasi berkas foto profil (format gambar `jpeg`, `png`, `jpg`, `webp` dengan ukuran maksimum 2 MB).
  4. Sistem menyimpan berkas foto ke media penyimpanan (*storage public*) dan menyimpan path berkas beserta seluruh atribut paslon ke tabel `candidates`.
- **Keluaran (Output):** Profil pasangan calon terdaftar pada katalog agenda pemilihan dan siap dirender pada kartu suara digital pemilih.
- **Validasi & Penolakan:**
  - Penolakan penyimpanan jika ukuran berkas foto melebihi 2 MB atau tipe MIME tidak valid.
  - Penolakan duplikasi nomor urut pasangan calon dalam satu agenda pemilihan yang sama (`unique:candidates,candidate_number,event_id`).
  - Penolakan penghapusan kandidat jika agenda pemilihan telah memiliki suara yang masuk pada tabel `votes`.

#### FR-04: Manajemen Master DPT & Impor/Ekspor CSV
- **Aktor:** Super Admin.
- **Masukan (Input):** Data pemilih individual (Nama lengkap, nomor identitas NISN/NIP, tipe pemilih: `SISWA`/`GURU`, kelas, jenis kelamin: `L`/`P`) atau berkas massal bertipe CSV (`.csv` / `.txt`).
- **Proses:**
  1. Untuk entri manual: sistem menyimpan data langsung ke tabel `voters` dengan status awal aktif (`is_active = true`).
  2. Untuk impor massal:
     - Sistem memvalidasi berkas unggahan (maksimal 5 MB, ekstensi CSV).
     - Sistem membuka berkas, menangani penanda byte order (*UTF-8 BOM*), mendeteksi pemisah kolom (*delimiter* koma/titik-koma), dan memverifikasi kesesuaian baris tajuk (*header*).
     - Sistem membaca baris demi baris dalam transaksi basis data, memvalidasi nomor identitas unik, mengonversi nilai jenis kelamin dan tipe pemilih ke format standar, serta menyisipkan data ke tabel `voters`.
  3. Untuk ekspor DPT: sistem menghasilkan stream berkas CSV ber-BOM UTF-8 agar kompatibel saat dibuka di Microsoft Excel tanpa mengalami kerusakan karakter (*encoding issues*).
- **Keluaran (Output):** Data master DPT tersimpan pada basis data, laporan ringkasan jumlah baris yang berhasil diimpor, serta berkas unduhan CSV DPT terformat.
- **Validasi & Penolakan:**
  - Penolakan data baris jika nomor identitas telah terdaftar sebelumnya (*duplicate identifier check*).
  - Penolakan format baris CSV jika tajuk kolom wajib (*identifier*, *name*, *type*) tidak ditemukan dalam berkas.

#### FR-05: Kolaborasi Kepanitiaan Berbasis Token Undangan
- **Aktor:** Super Admin, Panitia.
- **Masukan (Input):** Penugasan user panitia (`user_id`) ke agenda pemilihan tertentu (`event_id`) atau pembuatan tautan undangan kolaborasi.
- **Proses:**
  1. Melalui penetapan langsung: Super Admin memilih staf dari daftar `users` dan menetapkannya sebagai panitia agenda terkait pada tabel pivot `event_officers`.
  2. Melalui tautan undangan:
     - Sistem membangkitkan string acak 32 karakter kriptografis (`invite_token`) yang disimpan pada kolom `invite_token` di tabel `events`.
     - Panitia yang memiliki akun terdaftar dapat mengakses URL undangan (`/events/join/{invite_token}`).
     - Sistem memverifikasi validitas token, lalu secara otomatis mendaftarkan panitia tersebut ke tabel pivot `event_officers` untuk agenda terkait.
- **Keluaran (Output):** Hak akses operasional agenda pemilihan terbuka bagi panitia terkait, sehingga panitia dapat mengelola paslon, memantau perolehan suara, dan mengekspor dokumen resmi agenda tersebut.
- **Validasi & Penolakan:**
  - Penolakan penugasan jika akun pengguna yang ditunjuk bukan ber-role `panitia` atau `admin`.
  - Penolakan penggabungan panitia jika `invite_token` salah, telah di-regenerate oleh Super Admin, atau panitia telah terdaftar sebelumnya pada agenda tersebut.

#### FR-06: Akses Masuk Bilik Suara Mandiri & Penegakan Bilik
- **Aktor:** Pemilih (Siswa/Guru).
- **Masukan (Input):** Kode unik bilik suara (`room_code`, 6 digit alfanumerik) dan nomor identitas resmi (NISN/NIP).
- **Proses:**
  1. Pemilih membuka halaman bilik suara (`/voter/room`) dan memasukkan kode bilik agenda pemilihan yang sedang berlangsung.
  2. Sistem mencari agenda aktif pada tabel `events` yang memiliki `room_code` cocok.
  3. Middleware `voter.room` memastikan pemilih berada dalam sesi bilik yang sah dan menyimpan konteks `current_event_id` ke dalam session pemilih.
  4. Sistem memeriksa apakah pemilih memiliki relasi partisipasi pada tabel `event_participations` untuk agenda tersebut; jika belum ada, sistem menginisialisasi rekam partisipasi awal dengan status belum memilih (`has_voted = false`).
- **Keluaran (Output):** Layar bilik suara menampilkan halaman kartu suara digital yang memuat daftar pasangan calon kontestan lengkap dengan foto, nomor urut, serta tombol penampil visi-misi modal dialog.
- **Validasi & Penolakan:**
  - Penolakan akses jika kode bilik suara tidak ditemukan di basis data atau salah ketik.
  - Penolakan jika pemilih mencoba mengakses bilik suara agenda lain tanpa melalui proses verifikasi kode bilik suara yang sah.

#### FR-07: Mesin Validasi Hak Suara (*Eligibility Engine*)
- **Aktor:** Pemilih, Sistem (Otomatis).
- **Masukan (Input):** Objek data pemilih yang sedang login (`$voter`) dan agenda pemilihan yang dituju (`$event`).
- **Proses:**
  1. Sistem mengevaluasi method bisnis `Event::isVoterEligible($voter)` yang menjalankan rangkaian uji kelayakan berjenjang:
     - **Uji Status Akun:** Memastikan akun pemilih aktif (`is_active == true`).
     - **Uji Status Agenda:** Memastikan status agenda bernilai `PUBLISHED` dan waktu saat ini berada dalam rentang `start_time` hingga `end_time`.
     - **Uji Segmentasi Sasaran (*Target Scope*):**
       - Jika target `ALL`: seluruh pemilih (Siswa & Guru) dinyatakan lolos.
       - Jika target `GURU`: pemilih wajib memiliki tipe `type == 'GURU'`.
       - Jika target `SISWA`: pemilih wajib memiliki tipe `type == 'SISWA'`.
       - Jika target `KELAS`: pemilih wajib bertipe `SISWA` dan atribut `class` cocok dengan `target_class` agenda.
     - **Uji Status Partisipasi:** Memeriksa tabel pivot `event_participations`; pemilih dinyatakan gugur hak suaranya jika record partisipasi menunjukkan `has_voted == true`.
- **Keluaran (Output):** Status boolean kelayakan (`true`/`false`) beserta pesan diagnostik deskriptif bila tidak memenuhi syarat.
- **Validasi & Penolakan:**
  - Penolakan akses bilik jika pemilih tidak termasuk dalam segmentasi sasaran (misal: guru mencoba mencoblos pada pemilihan khusus kelas tertentu).
  - Pengalihan pemilih ke halaman informasi "Hak Suara Telah Digunakan" jika terdeteksi bahwa pemilih telah menggunakan hak pilihnya pada agenda tersebut.

#### FR-08: Eksekusi Pencoblosan Suara Anonim & Atomik
- **Aktor:** Pemilih.
- **Masukan (Input):** Pilihan kandidat pasangan calon terpilih (`candidate_id`) melalui konfirmasi modal surat suara digital.
- **Proses:**
  1. Sistem memverifikasi kembali kelayakan hak suara pemilih pada detik eksekusi.
  2. Sistem mendelegasikan pemrosesan kepada Action Class khusus `CastVoteAction` yang dibungkus dalam blok transaksi database ACID (`DB::transaction`).
  3. Sistem mengunci baris data partisipasi pemilih pada tabel `event_participations` menggunakan *pessimistic locking* (`lockForUpdate()`) guna mengeliminasi celah *race condition*.
  4. Sistem menyimpan surat suara digital ke tabel `votes`:
     - Kolom tersimpan: `event_id`, `candidate_id`, dan `created_at`.
     - Kolom `voter_id` **DITIADAKAN SAMA SEKALI** dari tabel `votes` untuk memastikan prinsip kerahasiaan (*anonymity*) suara terjamin secara struktural.
  5. Sistem memperbarui baris `event_participations`: status diubah menjadi `has_voted = true` dan mencatat waktu pencoblosan pada `voted_at`.
  6. Transaksi database di-commit secara atomik (seluruh query berhasil serentak atau dibatalkan total jika terjadi kegagalan sistem).
- **Keluaran (Output):** Suara sah bertambah 1 pada paslon terkait, sesi bilik suara pemilih ditutup, dan pemilih dialihkan ke layar ucapan terima kasih (*success voting page*).
- **Validasi & Penolakan:**
  - Pembatalan transaksi total (*rollback*) jika `candidate_id` tidak terdaftar atau bukan merupakan bagian dari agenda pemilihan terkait.
  - Penolakan transaksi jika pada saat penguncian baris ditemukan `has_voted` sudah bernilai `true`.

#### FR-09: Pencegahan Suara Ganda & Kendali Konkurensi
- **Aktor:** Sistem (Otomatis).
- **Masukan (Input):** Upaya pengiriman data suara (`POST /voter/vote`) yang dikirimkan berulang kali atau secara simultan.
- **Proses:**
  1. Mekanisme lapis pertama di antarmuka web (Frontend): Tombol konfirmasi pencoblosan langsung dinonaktifkan (*disabled state*) dan menampilkan animasi pemrosesan seketika setelah tombol ditekan untuk mencegah klik ganda (*double click*).
  2. Mekanisme lapis kedua di backend: Penguncian baris pesimistik (*pessimistic row locking*) pada transaksi basis data. Permintaan kedua yang tiba bersamaan akan antre menunggu transaksi pertama selesai. Saat giliran tiba, baris data telah berstatus `has_voted == true`, sehingga eksekusi kedua langsung dilempar (*throw*) ke penolakan eksepsi.
  3. Mekanisme lapis ketiga: Validasi konsistensi token CSRF unik per formulir pencoblosan.
- **Keluaran (Output):** Eksepsi keamanan `VotingException` dibangkitkan, permintaan kedua ditolak mentah-mentah, dan sistem mempertahankan integritas *one person one vote*.
- **Validasi & Penolakan:** Sistem menolak setiap upaya pencoblosan berikutnya dengan kode pesan "Hak suara Anda untuk pemilihan ini telah digunakan."

#### FR-10: Pemantauan Perolehan Suara Real-time (*Live Monitoring*)
- **Aktor:** Super Admin, Panitia.
- **Masukan (Input):** Pemilihan agenda yang ingin dipantau melalui antarmuka monitoring (`/admin/monitoring/{event}`).
- **Proses:**
  1. Sistem melakukan rekapitulasi komputasi agregasi basis data:
     - Menghitung total pemilih berhak suara (DPT *eligible*) berdasarkan konfigurasi `target_scope` agenda.
     - Menghitung total surat suara yang telah masuk ke tabel `votes` (`total_votes`).
     - Menghitung perolehan suara masing-masing pasangan calon beserta persentasenya terhadap total suara masuk.
     - Menghitung persentase tingkat partisipasi kehadiran pemilih (*voter turnout*).
  2. Sistem menyediakan endpoint API JSON `liveStats` yang di-polling secara berkala oleh frontend untuk memperbarui tampilan grafik dan angka metrik secara dinamis tanpa perlu me-refresh halaman web secara penuh.
- **Keluaran (Output):** Dasbor interaktif visual dengan diagram perolehan suara paslon, kartu indikator suara masuk, sisa suara belum memilih, dan persentase kehadiran pemilih.
- **Validasi & Penolakan:** Penolakan akses halaman pemantauan bagi pengguna yang tidak memiliki otorisasi (hanya Super Admin dan Panitia yang terdaftar pada agenda terkait yang berhak mengakses).

#### FR-11: Ekspor Berita Acara Resmi PDF Ber-Hash & Data CSV
- **Aktor:** Super Admin, Panitia.
- **Masukan (Input):** Permintaan ekspor dokumen hasil pemilihan dalam format Berita Acara PDF (`/admin/monitoring/{event}/export-pdf`) atau berkas rekapitulasi CSV (`/admin/monitoring/{event}/export-csv`).
- **Proses:**
  1. Untuk Berita Acara PDF:
     - Sistem mengambil snapshot rekapitulasi suara akhir agenda pemilihan.
     - Sistem menghitung nilai hash kriptografis SHA-256 berdasarkan gabungan ID agenda, total suara masuk, rincian perolehan tiap kandidat, dan stempel waktu ekspor.
     - Nilai hash kriptografis dicantumkan pada bagian kaki dokumen (*Digital Verification Hash*) sebagai segel integritas dokumen dari risiko pemalsuan cetak.
     - Sistem merender layout HTML ke dokumen cetak resmi PDF menggunakan library DomPDF.
  2. Untuk Rekapitulasi CSV:
     - Sistem menyusun baris tabel berisi nomor urut, nama ketua & wakil, jumlah perolehan suara, dan persentase suara.
     - Sistem menambahkan penanda *UTF-8 BOM* pada awal berkas agar dapat langsung dibuka secara rapi pada berbagai aplikasi spreadsheet (Microsoft Excel, LibreOffice Calc).
- **Keluaran (Output):** Berkas digital resmi Berita Acara Pemilihan (PDF ber-hash) yang siap dicetak dan ditandatangani panitia/saksi, serta berkas data mentah CSV.
- **Validasi & Penolakan:** Pembatasan akses ekspor hanya dapat dijalankan oleh panitia pengelola agenda atau Super Admin.

#### FR-12: Audit Trail Terperinci & Pemeliharaan Sistem
- **Aktor:** Super Admin.
- **Masukan (Input):** Parameter penelusuran audit log (filter berdasarkan waktu, jenis aksi, atau aktor pengguna), permintaan pembersihan log (*prune logs*), atau permintaan pembersihan cache (*clear cache*).
- **Proses:**
  1. **Pencatatan Otomatis (Audit Logging):** Setiap tindakan penting pada sistem (seperti login, pembuatan agenda, pembaruan status, penambahan kandidat, impor DPT, ekspor laporan) secara otomatis dicatat ke tabel `audit_logs` dengan merekam ID pengguna, tipe guard/aktor, nama aksi, alamat IP klien, data *user-agent* peramban, serta payload konteks terkait.
  2. **Pemeliharaan Log (*Pruning*):** Super Admin dapat menjalankan aksi pembersihan berkala untuk menghapus entri log audit yang telah berusia lebih dari rentang waktu tertentu (misal: lebih dari 30 hari) guna menjaga ukuran penyimpanan basis data tetap efisien.
  3. **Pemeliharaan Kinerja (*Clear Cache*):** Super Admin dapat memicu utilitas pembersihan cache internal aplikasi (menjalankan perintah terisolasi `optimize:clear`) untuk menyegarkan cache konfigurasi, cache view Blade, dan cache rute.
- **Keluaran (Output):** Antarmuka tabel riwayat audit trail yang komprehensif, notifikasi konfirmasi pembersihan log, serta konfirmasi status pembersihan cache sistem.
- **Validasi & Penolakan:** Akses modul audit trail dan utilitas pemeliharaan dibatasi secara eksklusif hanya untuk aktor ber-role Super Admin; panitia biasa maupun pemilih diblokir secara absolut melalui middleware `auth` dan pengecekan role.

---

## 2.3 Kebutuhan Non-Fungsional Sistem (Non-Functional Requirements)

Kebutuhan non-fungsional mendefinisikan batasan kualitas, keandalan, keamanan, dan kinerja yang wajib dipenuhi oleh sistem agar dapat beroperasi secara optimal, stabil, dan tepercaya.

### 2.3.1 Parameter Keamanan Sistem (Security Requirements)
Keamanan merupakan aspek paling krusial dalam sistem *e-voting*. Sistem menerapkan pertahanan berlapis (*defense-in-depth*):

1. **Perlindungan terhadap OWASP Top 10 Vulnerabilities:**
   - **Cross-Site Request Forgery (CSRF):** Seluruh permintaan HTTP mutasi data (`POST`, `PUT`, `DELETE`) wajib menyertakan token CSRF yang divalidasi oleh *middleware* bawaan Laravel (`ValidateCsrfToken`).
   - **Cross-Site Scripting (XSS):** Seluruh keluaran teks pada antarmuka web disanitasi secara otomatis menggunakan *Blade Templating Engine syntax* `{{ $data }}` yang melakukan escaping entitas HTML (`htmlspecialchars`).
   - **SQL Injection:** Seluruh interaksi basis data menggunakan *Eloquent ORM* dan *Query Builder* yang memanfaatkan *PDO Prepared Statements* dengan *parameter binding*, sehingga perintah SQL terisolasi mutlak dari manipulasi input pengguna.
2. **Kriptografi & Penyimpanan Data Sensitif:**
   - Kata sandi pengguna internal (Super Admin dan Panitia) dienkripsi satu arah menggunakan fungsi hash `Bcrypt` dengan *cost factor* default 12.
   - Kode unik bilik suara (`room_code` 6 karakter *uppercase*) dan token tautan undangan panitia (`invite_token` 32 karakter acak) dihasilkan secara deterministik menggunakan generator pseudo-random yang aman secara kriptografis (`Str::random()`).
   - Seluruh berkas resmi Berita Acara PDF yang diekspor dilengkapi tanda tangan digital berupa nilai *Digital Verification Hash* berbasis algoritma kriptografis SHA-256 (`hash('sha256', ...)`) untuk menjamin dokumen cetak bebas dari pemalsuan rekapitulasi.
3. **Anonimitas Suara Mutlak (Secret Ballot Integrity):**
   - Struktur data pemungutan suara menjamin tidak adanya relasi balik (*non-traceability*) antara baris data suara pada tabel `votes` dengan identitas individu pada tabel `voters`. Tabel `votes` murni hanya mencatat `event_id`, `candidate_id`, dan *timestamp*, tanpa menyimpan *foreign key* `voter_id` maupun jejak IP address pemilih.
4. **Proteksi Brute Force & Rate Limiting:**
   - Rute masuk (*login routes*) dibatasi maksimal 5 percobaan per menit per alamat IP menggunakan fitur *Rate Limiting Throttle* Laravel guna mencegah upaya peretasan berbasis kamus (*dictionary attack*) atau tebakan massal.

---

### 2.3.2 Integritas Data & Konkurensi (Concurrency & Data Integrity)
Pada hari pemungutan suara, puluhan hingga ratusan pemilih dapat mengakses bilik suara secara bersamaan di laboratorium sekolah. Oleh karena itu, integritas data dijamin melalui:

1. **Kepatuhan Prinsip ACID (Atomicity, Consistency, Isolation, Durability):**
   - Seluruh alur penyimpanan suara dibungkus dalam blok transaksi database `DB::transaction()` melalui service `CastVoteAction`. Jika salah satu query penyimpanan atau pembaruan status mengalami kegagalan, seluruh rangkaian operasi dibatalkan secara otomatis (*rollback*) ke kondisi semula.
2. **Pessimistic Locking (`lockForUpdate`):**
   - Saat pemilih mengonfirmasi pencoblosan, baris keikutsertaan pemilih pada tabel pivot `event_participations` dikunci secara eksklusif pada tingkat basis data (`lockForUpdate()`) sebelum status `has_voted` diperiksa. Hal ini mengeliminasi risiko *race condition* di mana seorang pemilih mencoba mengirimkan suara ganda secara bersamaan dari dua peramban atau tab berbeda.
3. **Integritas Relasional & Unique Constraints:**
   - Kolom `identifier` (NISN/NIP) pada tabel `voters` memiliki indeks `UNIQUE` untuk mencegah duplikasi akun pemilih.
   - Pasangan komposit `(event_id, voter_id)` pada tabel `event_participations` diikat dengan batasan unik untuk memastikan satu pemilih hanya terdaftar satu kali per agenda pemilihan.
   - Pasangan komposit `(event_id, candidate_number)` pada tabel `candidates` memiliki indeks `UNIQUE` untuk mencegah nomor urut paslon yang bertabrakan dalam satu agenda pemilihan.
   - Kolom `room_code` dan `invite_token` pada tabel `events` dijamin unik melalui indeks `UNIQUE`.

---

### 2.3.3 Kinerja dan Efisiensi Sistem (Performance Requirements)
1. **Waktu Respon Cepat (Low Latency):**
   - Waktu muat halaman antarmuka bilik suara dan proses transaksi pencoblosan suara tidak boleh melebihi **500 milidetik** pada jaringan lokal (LAN) sekolah.
2. **Efisiensi Pengambilan Data (Query Optimization):**
   - Menerapkan teknik *Eager Loading* (`with()`) pada pemanggilan relasi Eloquent (seperti relasi pasangan calon dan data pemilihan) guna mengeliminasi masalah performa $N+1$ *query problem*.
   - Menerapkan indeks basis data (*database index*) pada kolom-kolom yang sering dijadikan filter dan relasi, seperti `event_id`, `has_voted`, `status`, dan `voter_type`.
3. **Aset Frontend Ringan & Visualisasi Reaktif:**
   - Bundling aset CSS dan JavaScript menggunakan *Vite* yang menghasilkan berkas terkompresi dan termodifikasi (*minified*).
   - Penggunaan *Chart.js* dan *Alpine.js* untuk menampilkan visualisasi grafik perolehan suara secara interaktif dan ringan tanpa membebani memori peramban klien.

---

### 2.3.4 Kebutuhan Kemudahan Penggunaan (Usability Requirements)
1. **Antarmuka Intuitif & Bebas Distraksi:**
   - Antarmuka bilik suara dirancang terisolasi (*kiosk-mode experience*), menonjolkan foto pasangan calon, nomor urut yang kontras, serta nama ketua & wakil ketua yang terbaca jelas oleh seluruh siswa.
2. **Alur Pemilihan Sederhana (3 Langkah Singkat):**
   - Alur pemilih dibatasi hanya dalam 3 langkah terarah: **(1) Masuk Bilik Suara (Input NISN/NIP & Kode Ruang) -> (2) Pilih Pasangan Calon -> (3) Konfirmasi & Finalisasi Pilihan**.
3. **Pencegahan Kesalahan Manusia (Error Prevention):**
   - Tombol pencoblosan dilengkapi modal konfirmasi bertingkat ("*Apakah Anda yakin memilih Pasangan Calon No. X?*") dengan menampilkan kembali nama paslon sebelum transaksi suara diproses secara permanen.
4. **Desain Responsif (Responsive Design):**
   - Sistem responsif pada berbagai ukuran layar (monitor PC desktop lab sekolah, laptop, tablet, maupun layar *smartphone*) menggunakan utilitas Tailwind CSS berbasis Flexbox dan Grid.

---

### 2.3.5 Keandalan dan Ketersediaan (Reliability & Availability)
1. **Toleransi Kesalahan (Graceful Exception Handling):**
   - Sistem menangkap semua kesalahan eksepsi sistem (*unhandled exception*) dan menampilkan pesan informatif yang ramah pengguna, bukan halaman *debug stack-trace* bawaan server yang membingungkan pemilih.
2. **Pencadangan Data Cepat (Data Backup Ready):**
   - Skema basis data mendukung pencadangan kilat (*database dump*) yang dapat dijalankan oleh Super Admin setiap jam selama periode pemilihan berlangsung.
3. **Pembersihan Rutin (Audit Pruning & Cache Clearing):**
   - Sistem menyediakan modul bawaan bagi Super Admin untuk membersihkan log audit lama (*pruning*) dan menyegarkan cache aplikasi secara instan demi menjaga stabilitas kinerja server.

---

## 2.4 Kebutuhan Lingkungan Sistem

Kebutuhan lingkungan sistem mendefinisikan prasyarat teknologi perangkat lunak (*software stack*), spesifikasi perangkat keras (*hardware*), serta pemetaannya terhadap standar uji kompetensi.

### 2.4.1 Kebutuhan Perangkat Lunak (Software Requirements)

Berikut adalah tumpukan teknologi (*technology stack*) faktual yang digunakan dalam pengembangan dan operasional sistem:

| Komponen Lingkungan | Perangkat Lunak / Teknologi | Versi Faktual | Fungsi & Peran dalam Sistem |
| :--- | :--- | :--- | :--- |
| **Sistem Operasi Server** | Windows 10/11 / Ubuntu Server / Debian | Win 10+ / Linux 20.04+ | Lingkungan *hosting* peladen web dan basis data. |
| **Bahasa Pemrograman** | PHP (Hypertext Preprocessor) | PHP 8.3+ | Bahasa pemrograman utama sisi backend (*server-side*). |
| **Framework Backend** | Laravel Framework | Versi 13.x (Terbaru) | Kerangka kerja MVC untuk routing, controller, ORM, dan autentikasi. |
| **Basis Data Relasional** | MySQL / MariaDB (InnoDB Engine) | MySQL 8.0+ / MariaDB 10.4+ | Penyimpanan data relasional dengan dukungan transaksi ACID dan *row locking*. |
| **Server Web Lokal** | PHP Built-in Server / Nginx / Apache | Versi Terkini | Melayani permintaan HTTP/HTTPS dari pengguna. |
| **Manajer Dependensi PHP** | Composer | Versi 2.6+ | Manajemen pustaka dan dependensi paket PHP. |
| **Runtime JavaScript** | Node.js & NPM | Node.js v18.x+ / NPM v9.x+ | Lingkungan eksekusi alat kompilasi aset frontend. |
| **Frontend Framework** | Tailwind CSS & Alpine.js | Tailwind v4.x, Alpine v3.17+ | Desain antarmuka responsif modern dan reaktivitas interaksi modal/tab. |
| **Grafik & Visualisasi** | Chart.js | Versi 4.5+ | Rendering diagram batang dan donat perolehan suara *real-time*. |
| **Library Dokumen PDF** | Barryvdh Laravel-DomPDF | Versi 3.1+ | Pustaka pembuat dokumen cetak resmi Berita Acara PDF ber-hash SHA-256. |
| **Alat Build Frontend** | Vite | Versi 8.x / Modern Bundler | Bundler cepat untuk kompilasi aset frontend dan *hot module replacement*. |
| **Sistem Kontrol Versi** | Git & GitHub | Git 2.40+ | Manajemen kode sumber, pelacakan riwayat commit, dan kolaborasi. |
| **Browser Klien** | Google Chrome, Edge, Safari, Firefox | Versi Modern Terkini | Peramban web sisi pemilih dan administrator. |

---

### 2.4.2 Kebutuhan Perangkat Keras (Hardware Requirements)

Sistem dirancang efisien sehingga dapat berjalan dengan lancar pada perangkat komputer berspesifikasi standar yang umum tersedia di sekolah:

1. **Perangkat Keras Peladen (Server Host / Komputer Utama TPS):**
   - **Prosesor (CPU):** Minimal Intel Core i3 Generasi ke-6 / AMD Ryzen 3 atau setara (Direkomendasikan Intel Core i5 / Ryzen 5).
   - **Memori Utama (RAM):** Minimal 4 GB DDR4 (Direkomendasikan 8 GB untuk melayani hingga 50-100 koneksi bersamaan).
   - **Media Penyimpanan:** Minimal 20 GB ruang kosong (Direkomendasikan menggunakan Solid State Drive / SSD untuk kecepatan baca-tulis basis data).
   - **Konektivitas Jaringan:** Kartu jaringan LAN Gigabit Ethernet (RJ-45) atau Adaptor Wi-Fi Dual-Band (2.4 GHz / 5 GHz) dengan router lokal berkecepatan stabil.

2. **Perangkat Keras Klien (Bilik Suara Siswa & Meja Registrasi Petugas):**
   - **Perangkat:** Komputer PC Desktop Lab, Laptop, atau Tablet Android/iPad.
   - **Prosesor (CPU):** Minimal Dual-Core 1.8 GHz.
   - **Memori Utama (RAM):** Minimal 2 GB.
   - **Layar:** Resolusi minimal 1024 x 768 piksel (responsif pada layar sentuh *smartphone* 360 x 640 piksel).
   - **Perangkat Masukan:** Keyboard dan mouse standar, atau layar sentuh (*touchscreen*).

---

### 2.4.3 Pemetaan Terhadap SKKNI LSP P3 Pemrogram Junior

Penyusunan analisis kebutuhan pada Bab II ini secara langsung mengimplementasikan dan membuktikan pemenuhan unit kompetensi SKKNI Pemrogram Junior, antara lain:

| Kode Unit SKKNI | Judul Unit Kompetensi | Bukti Implementasi Nyata dalam Sistem E-Voting |
| :--- | :--- | :--- |
| **J.620100.001.01** | Menerapkan Perintah Eksekusi Bahasa Pemrograman Berbasis Teks | Penggunaan sintaks murni PHP 8.3+ dan struktur kontrol Laravel dalam menangani logika bisnis pemilihan (`CastVoteAction`, `VoterImportService`). |
| **J.620100.003.01** | Menerapkan Struktur Data | Pengorganisasian data kandidat, data pemilih, dan transaksi suara menggunakan relasi tabel Eloquent, array associative, dan manipulasi Laravel Collection. |
| **J.620100.009.01** | Menggunakan Spesifikasi Program | Penerjemahan spesifikasi kebutuhan pengguna menjadi rancangan fungsionalitas sistem terinci (FR-01 s/d FR-12) dan batasan non-fungsional (NFR). |
| **J.620100.011.01** | Melakukan Pengujian Program | Penyusunan skenario validasi, penolakan input tidak sah, serta pengujian mekanisme anti-suara ganda (*anti-double voting*) untuk menjamin keandalan sistem. |
