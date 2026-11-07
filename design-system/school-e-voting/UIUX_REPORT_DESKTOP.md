# Laporan Rancangan UI/UX & Spesifikasi Sistem (Versi Desktop)

**Proyek:** E-Voting Sekolah (Digital Election System)  
**Target Viewport:** Desktop (1440px &times; 900px, Grid 12 Kolom)  
**Status Implementasi:** Sesuai 100% dengan Kode Produksi Laravel & Tailwind v4  
**File Canvas Siap Figma:** [public/figma-uiux-report.html](../../public/figma-uiux-report.html) (`http://127.0.0.1:8000/figma-uiux-report.html`)

---

## 1. Fondasi Desain & Design Tokens

### 1.1 Palet Warna Resmi
| Peran Token | Nilai Hex | Variabel CSS | Penggunaan di Antarmuka |
|---|---|---|---|
| **Navy 900 (Main)** | `#0F172B` | `--color-navy-900` | Sidebar admin, nomor kandidat, tombol primary utama |
| **Navy 800 (Hover)** | `#1E293B` | `--color-navy-800` | State aktif menu navigasi, border kontras |
| **Civic Blue (Accent)** | `#2563EB` | `--color-primary` | Focus ring, turnout progress bar, tautan aksi |
| **Emerald 500 (Success)** | `#10B981` | `--color-accent` | Badge bilik suara aktif, indikator paslon terpilih |
| **Amber 500 (Warning)** | `#F59E0B` | N/A | Badge status draft, peringatan verifikasi suara |
| **Rose 600 (Danger)** | `#E11D48` | `--color-destructive` | Tombol batalkan agenda, logout |
| **Slate 50 (Surface)** | `#F8FAFC` | `--color-background` | Background kanvas aplikasi (mencegah kelelahan mata) |
| **Card White** | `#FFFFFF` | `--color-card` | Kontainer kartu kandidat, panel metrik |
| **Border Slate** | `#E2E8F0` | `--color-border` | Pembatas kartu, pemisah header & baris |

### 1.2 Skala Tipografi (Keluarga Huruf: Inter)
- **H1 (Judul Utama):** Inter Bold, 24px - 30px, Line-height 1.2, Letter-spacing -0.02em.
- **H2 (Subjudul Section):** Inter Bold, 18px - 20px, Line-height 1.3, Letter-spacing -0.015em.
- **H3 (Judul Kartu/Paslon):** Inter SemiBold, 16px - 18px.
- **Body Regular:** Inter Regular/Medium, 14px, Line-height 1.5, Warna `#475569`.
- **Caption & Meta:** Inter Medium, 11px - 12px, Warna `#64748B`.
- **Tabular Figures (Angka Stat/Kode):** Tabular Nums / Monospace (`#3ZYYWL`, `82.6%`, `01`, `02`).

### 1.3 Grid & Spacing
- **Container Max-Width:** 1280px / 1440px.
- **Margin/Padding Standar:** 16px (1rem), 24px (1.5rem), 32px (2rem).
- **Radius Sudut (Border Radius):** 16px (`rounded-2xl`) untuk kartu utama dan modal; 12px (`rounded-xl`) untuk tombol dan input fields; 8px (`rounded-lg`) untuk pill badge.

---

## 2. Struktur Frame Figma & Layar Desktop

### Frame 01: Fondasi Token & Komponen
- Menampilkan color swatch lengkap dengan nilai Hex dan label token.
- Komponen tombol (Primary Navy, Accent Blue, Secondary Outline, Destructive).
- Status badge (Pill: Aktif, Draft, Selesai).
- Form input & password toggler.

### Frame 02: Dashboard Admin & Panitia (Desktop 1440px)
- **Sidebar Kiri (256px):** Warna latar `#0F172B`, logo E-Voting, navigasi berikon (Dashboard, Agenda Pemilihan, Data Pemilih, Panitia & Admin, Log Audit), identitas user Super Admin di bagian bawah.
- **Top Header (64px):** Breadcrumb navigasi, indikator sistem, tombol aksi utama `+ Buat Agenda`.
- **Hero Welcome Card:** Greeting personalisasi panitia dan status tahun ajaran.
- **Grid KPI Metrik (4 Kolom):** Total Agenda (4), Bilik Suara Aktif (1 - Emerald), Draft (2 - Amber), Pemilihan Selesai (1 - Slate).
- **Kartu Agenda Pemilihan:** Kartu dua kolom dengan progress bar persentase partisipasi suara (*Turnout Rate*), jumlah paslon, dan tombol akses cepat.

### Frame 03: Bilik Suara Pemilih / Voting Booth (Desktop 1440px)
- **Header Bilik:** Brand institusi sekolah, identitas pemilih aktif (Nama, Kelas/NISN), tombol Keluar Bilik.
- **Banner Pemilihan:** Kode bilik `#3ZYYWL`, kategori pemilihan, indikator sesi aktif, kotak ketentuan pemilihan (1 pemilih = 1 hak suara).
- **Grid Pasangan Calon (2 Kolom Seimbang):**
  - Nomor urut kontras tinggi (`01`, `02`) dengan latar `#0F172B`.
  - Foto paslon dengan aspect ratio 4:3.
  - Nama kandidat & visi-misi ringkas dalam drawer/accordion.
  - Tombol aksi utama: `Pilih Paslon #[Nomor Urut]`.

### Frame 04: State Modal Konfirmasi Pilihan Suara
- **Backdrop:** `#0F172B` dengan opacity 60% dan backdrop-blur.
- **Dialog Modal (Max-width 480px):**
  - Badge nomor urut paslon terpilih.
  - Ringkasan nama dan visi misi paslon yang dipilih.
  - Kotak peringatan verifikasi final (suara tidak dapat diubah setelah konfirmasi).
  - Aksi: Tombol Batal & Tombol `Ya, Tetapkan Pilihan`.

---

## 3. Cara Membawa ke Kanvas Figma

Ada 2 cara yang dapat digunakan:
1. **Menggunakan Figma MCP Server (Otomatis):**
   - Pastikan server lokal tetap aktif di port `8000`.
   - Gunakan file URL: `http://127.0.0.1:8000/figma-uiux-report.html`.
   - Perintahkan di chat: *"Generate figma design dari URL http://127.0.0.1:8000/figma-uiux-report.html ke file Figma saya [URL_FIGMA]"* atau ke *drafts baru*.
2. **Menggunakan Plugin "HTML to Figma" / "Builder.io" di Figma:**
   - Di aplikasi Figma, buka plugin **HTML to Figma** atau **html.to.design**.
   - Masukkan link: `http://127.0.0.1:8000/figma-uiux-report.html`.
   - Seluruh frame desktop 1440px langsung terkonversi menjadi Auto-layout Figma layer secara presisi.
