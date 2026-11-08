# PANDUAN DAN KODE KONVERSI DIAGRAM DRAW.IO (SUB-BAB 3.1)
## Laporan UKOM LSP P3 - Sistem E-Voting Berbasis Web

Dokumen ini memuat kode diagram siap pakai untuk **Draw.io (diagrams.net)** yang dirancang khusus untuk memvisualisasikan **Sub-bab 3.1 (Perancangan Arsitektur Sistem dan Keamanan)**.

---

### CARA MEMASUKKAN KODE KE DRAW.IO (Langkah 10 Detik)

1. Buka peramban dan akses **[https://app.diagrams.net](https://app.diagrams.net)** (atau buka aplikasi desktop Draw.io).
2. Buat dokumen baru (*Create New Diagram*) pilih **Blank Diagram**.
3. Pada menu bar atas Draw.io:
   - Klik ikon **`+`** (pada toolbar) **ATAU** klik menu **Arrange** -> **Insert** -> **Advanced** -> **Mermaid**.
4. Hapus teks bawaan pada kotak dialog yang muncul, lalu **Copy-Paste** salah satu blok kode diagram di bawah ini.
5. Klik tombol **Insert**.
6. Diagram akan langsung terbentuk menjadi komponen kotak dan panah Draw.io asli. Anda dapat mengatur tata letak, mengubah warna tema, mengganti jenis huruf (*font*), dan mengekspornya ke format **PNG (300 DPI)** atau **PDF** untuk dilampirkan ke dokumen laporan Word.

---

## DIAGRAM 1: ARSITEKTUR MULTI-TIER & POLA SISTEM (THREE-TIER ARCHITECTURE)

Diagram ini memvisualisasikan pemisahan tiga lapisan (*Presentation*, *Application*, dan *Data Persistence Tier*) serta interaksi antar-komponen Controller, Action (`CastVoteAction`), dan Model Eloquent.

### Kode Draw.io (Mermaid):
```mermaid
graph TB
    %% 1. PRESENTATION TIER
    subgraph Presentation_Tier ["1. PRESENTATION TIER (CLIENT INTERFACE)"]
        UI_Web["Web Browser Client<br/>(Blade Templates, Tailwind CSS v4, Alpine.js)"]
        UI_Kiosk["Bilik Suara Terminal<br/>(Responsive Shared Kiosk View)"]
        UI_API["Third-Party Client<br/>(RESTful JSON Consumer)"]
    end

    %% 2. APPLICATION & LOGIC TIER
    subgraph Logic_Tier ["2. APPLICATION & LOGIC TIER (LARAVEL 13 ENGINE)"]
        Router["Routing & Middleware Pipeline<br/>(Role Check, VoterAuth, CSRF, Throttling)"]
        
        Ctrl_Admin["Admin & Staf Controllers<br/>(Event, Candidate, Voter, AuditLog)"]
        Ctrl_Booth["Voter Booth Controller<br/>(VotingController, VoterAuthController)"]
        Ctrl_Mon["Monitoring & Report Controller<br/>(Live Polling, DomPDF Generator)"]
        Ctrl_API["API Controllers<br/>(v1/Admin, v1/Petugas, v1/Voter)"]

        Act_Vote["CastVoteAction<br/>(Atomic Transaction & Pessimistic Lock)"]

        Models["Eloquent Domain Models<br/>(User, Voter, Event, Candidate, Vote, Participation)"]
    end

    %% 3. DATA & PERSISTENCE TIER
    subgraph Data_Tier ["3. DATA & PERSISTENCE TIER (STORAGE LAYER)"]
        DB_Relational[("Relational Database<br/>(MySQL / MariaDB / SQLite)")]
        File_Storage["Public File Storage<br/>(storage/app/public/candidates)"]
        Session_Store["Session & Cache Engine<br/>(Database / File Driver)"]
    end

    %% HUBUNGAN ANTAR LAYER
    UI_Web --> Router
    UI_Kiosk --> Router
    UI_API --> Router

    Router --> Ctrl_Admin
    Router --> Ctrl_Booth
    Router --> Ctrl_Mon
    Router --> Ctrl_API

    Ctrl_Admin --> Models
    Ctrl_Mon --> Models
    Ctrl_Booth --> Act_Vote
    Ctrl_API --> Act_Vote
    Ctrl_API --> Models
    Act_Vote --> Models

    Models --> DB_Relational
    Ctrl_Admin -.-> File_Storage
    Router -.-> Session_Store
```

---

## DIAGRAM 2: SEQUENCE DIAGRAM OTENTIKASI MULTI-GUARD & SESI BILIK SUARA

Diagram urutan ini menggambarkan interaksi dinamis antara Pemilih, Kiosk Bilik Suara, Middleware, Controller, Action Transaksi, dan Basis Data pada tiga fase: Masuk Bilik, Pencoblosan Suara, dan Keluar Otomatis.

### Kode Draw.io (Mermaid):
```mermaid
sequenceDiagram
    autonumber
    actor Voter as Pemilih (Siswa / Guru)
    participant UI as Kiosk Bilik Suara
    participant Router as Routing & Middleware
    participant AuthCtrl as VoterAuthController
    participant VotingCtrl as VotingController
    participant Action as CastVoteAction
    participant DB as Database (InnoDB)

    Note over Voter, DB: FASE 1: MASUK BILIK SUARA (AUTENTIKASI GUARD VOTER)
    Voter->>UI: Input NISN / NIP & Kode Bilik (room_code)
    UI->>Router: POST /login (voter.login.submit)
    Router->>AuthCtrl: login(request)
    AuthCtrl->>DB: Validasi Identitas Voter (is_active = true)
    AuthCtrl->>DB: Validasi Event (room_code, isOngoing, target_scope)
    AuthCtrl->>DB: Cek Status Partisipasi (has_voted)
    
    alt Tidak Memenuhi Syarat / Sudah Memilih / Di Luar Jadwal
        AuthCtrl-->>UI: Redirect back dengan pesan error
        UI-->>Voter: Tampilkan pesan penolakan akses
    else Data Valid & Berhak Memilih
        AuthCtrl->>AuthCtrl: Auth::guard('voter')->login(voter)
        AuthCtrl->>AuthCtrl: session()->regenerate()
        AuthCtrl->>AuthCtrl: session(['voter_room_code' => room_code])
        AuthCtrl-->>UI: Redirect ke /bilik-suara/{roomCode}
    end

    Note over Voter, DB: FASE 2: PROSES PENCOBLOSAN (TRANSAKSI ATOMIK ACID)
    UI->>Router: GET /bilik-suara/{roomCode} (voter.booth)
    Router->>VotingCtrl: showBooth(roomCode)
    VotingCtrl->>DB: Ambil Daftar Kandidat Paslon
    VotingCtrl-->>UI: Tampilkan Kartu Suara Pasangan Calon
    Voter->>UI: Pilih Paslon & Konfirmasi Coblos
    UI->>Router: POST /bilik-suara/{roomCode}/vote (voter.vote.submit)
    Router->>VotingCtrl: submitVote(request, roomCode)
    VotingCtrl->>Action: execute(event, voter, candidateId)
    
    Note over Action, DB: Pessimistic Row Locking (Anti-Race Condition)
    Action->>DB: lockForUpdate() pada event_participations
    Action->>DB: lockForUpdate() pada candidates (validasi event_id)
    Action->>DB: INSERT INTO votes (event_id, candidate_id, voted_at) [ANONIM]
    Action->>DB: UPDATE event_participations (has_voted=true, voted_at=now)
    Action-->>VotingCtrl: Transaksi Selesai (Vote Recorded)

    Note over Voter, DB: FASE 3: TERMINASI OTOMATIS & PENGHANGUSAN SESI
    VotingCtrl->>VotingCtrl: Flash notifikasi sukses
    VotingCtrl-->>UI: Redirect ke /bilik-suara/{roomCode}/sukses
    UI-->>Voter: Tampilkan Layar Terima Kasih & Konfirmasi
    Voter->>UI: Klik Tombol Selesai / Logout
    UI->>Router: POST /logout (voter.logout)
    Router->>AuthCtrl: logout(request)
    AuthCtrl->>AuthCtrl: Auth::guard('voter')->logout()
    AuthCtrl->>AuthCtrl: session()->invalidate()
    AuthCtrl->>AuthCtrl: session()->regenerateToken()
    AuthCtrl-->>UI: Redirect ke Halaman Login Bilik
    UI-->>Voter: Bilik suara bersih siap untuk pemilih selanjutnya
```

---

## DIAGRAM 3: CLASS DIAGRAM KOTAK SUARA ANONIM (ZERO-KNOWLEDGE BALLOT ARCHITECTURE)

Diagram kelas ini memperlihatkan pemisahan fisik antara data kehadiran pemilih (`event_participations`) dengan kotak suara digital (`votes`), membuktikan bahwa sistem menjamin kerahasiaan pilihan pemilih (asas Rahasia) secara struktural.

### Kode Draw.io (Mermaid):
```mermaid
classDiagram
    direction LR

    class Voter {
        +bigint id
        +string identifier
        +string nama
        +string type
        +string kelas
        +boolean is_active
        +getNisnAttribute()
        +getNipAttribute()
    }

    class EventParticipation {
        +bigint id
        +bigint event_id
        +bigint voter_id
        +boolean has_voted
        +timestamp voted_at
    }

    class Event {
        +bigint id
        +string title
        +string category
        +string room_code
        +string target_scope
        +string target_class
        +timestamp start_time
        +timestamp end_time
        +string status
        +isOngoing()
    }

    class Vote {
        +bigint id
        +bigint event_id
        +bigint candidate_id
        +timestamp voted_at
    }

    class Candidate {
        +bigint id
        +bigint event_id
        +integer candidate_number
        +string name
        +string photo_url
        +text description
        +text vision
        +text mission
        +getChairmanNameAttribute()
        +getViceChairmanNameAttribute()
        +getVotesCountAttribute()
    }

    Voter "1" -- "0..*" EventParticipation : dicatat status hak suaranya
    Event "1" -- "0..*" EventParticipation : memiliki daftar kehadiran
    Event "1" -- "0..*" Candidate : memiliki kontestan paslon
    Candidate "1" -- "0..*" Vote : menerima surat suara masuk
    Event "1" -- "0..*" Vote : mengumpulkan kotak suara
```

---

## DIAGRAM 4: ALUR PEMBUATAN SEGEL INTEGRITAS DIGITAL SHA-256 BERITA ACARA PDF

Diagram alir (*flowchart*) ini memvisualisasikan bagaimana kode segel integritas digital 32 karakter pada lembar Berita Acara PDF dihitung untuk mencegah pemalsuan dokumen fisik hasil pemungutan suara.

### Kode Draw.io (Mermaid):
```mermaid
flowchart TD
    Start([Mulai: Staf Mengunduh Berita Acara PDF]) --> InputData[/Ambil Data Hasil Pemilihan/]
    
    InputData --> Extract[Ekstrak Variabel Kunci: <br/>- Event ID <br/>- Total Suara Sah <br/>- ID Kandidat Terpilih <br/>- Kunci Rahasia Sistem APP_KEY]
    
    Extract --> Concat[Penggabungan String: <br/>rawVerification = EventID | TotalVotes | LeaderID | AppKey]
    
    Concat --> Hash[Kalkulasi Kriptografis: <br/>integrityHash = hash 'sha256', rawVerification]
    
    Hash --> Substr[Ekstraksi 32 Karakter Pertama & Kapitalisasi: <br/>digitalSeal = strtoupper substr integrityHash, 0, 32]
    
    Substr --> GenNo[Format Nomor Berita Acara: <br/>BA-EVT / Tahun / EventID]
    
    GenNo --> RenderPDF[Render Template DomPDF: <br/>Cetak Tabel Hasil, Nama Saksi, dan Kode Segel Integritas]
    
    RenderPDF --> OutputPDF[/File Berita Acara Resmi PDF Siap Diunduh & Dicetak/]
    OutputPDF --> End([Selesai])
```

---

### PANDUAN PEMBERIAN GAYA (STYLING) DI DRAW.IO UNTUK LAPORAN UKOM

Agar diagram terlihat rapi dan elegan saat diuji oleh Asesor LSP:
1. **Warna Kotak (Color Palette):**
   - **Presentation Layer / UI:** Gunakan warna biru muda (*Soft Blue*: `#dae8fc`, border `#6c8ebf`).
   - **Application Logic / Controller / Action:** Gunakan warna slate/abu-abu netral (*Light Gray/Slate*: `#f5f5f5`, border `#666666`).
   - **Data / Database Tier:** Gunakan warna hijau lembut (*Soft Green*: `#d5e8d4`, border `#82b366`).
   - **Blok Kritis / Keamanan (Zero-Knowledge & Lock):** Gunakan aksen oranye atau kuning lembut (*Warm Yellow*: `#fff2cc`, border `#d6b656`).
2. **Font & Tipografi:**
   - Gunakan jenis huruf sans-serif formal seperti **Arial**, **Helvetica**, atau **Inter**.
   - Ukuran judul kontainer: 12-14 pt (Tebal / *Bold*).
   - Ukuran teks komponen: 10-11 pt.
3. **Ekspor Gambar:**
   - Pilih menu **File** -> **Export as** -> **PNG**.
   - Centang **Transparent Background** (opsional) atau biarkan putih.
   - Atur **DPI** ke **300** agar saat disisipkan ke naskah skripsi/laporan Word hasilnya tajam dan tidak pecah saat dicetak.
