<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara - {{ $event->title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h4 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 9pt;
            font-style: italic;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-section h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-section .doc-number {
            font-size: 10pt;
            margin-top: 3px;
        }
        p.justified {
            text-align: justify;
            text-indent: 30px;
            margin: 10px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 10pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .winner-box {
            border: 2px solid #1e40af;
            background-color: #eff6ff;
            padding: 10px 14px;
            margin: 14px 0;
            font-size: 10.5pt;
        }
        .signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        .sign-space {
            height: 60px;
        }
        .stamp-box {
            margin-top: 25px;
            padding: 8px 12px;
            border: 1px dashed #64748b;
            font-family: monospace;
            font-size: 8pt;
            color: #475569;
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT RESMI -->
    <div class="header">
        <h4>ORGANISASI KESISWAAN DAN PANITIA PEMILIHAN</h4>
        <h3>KOMISI PEMILIHAN KETUA & WAKIL KETUA (E-VOTING)</h3>
        <h4>SMK / SMA NEGERI INDONESIA</h4>
        <p>Jalan Pendidikan No. 1, Kota Pelajar • Surel: panitia.evoting@sekolah.sch.id • Sistem E-Voting Digital</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="title-section">
        <h2>BERITA ACARA HASIL PEMILIHAN ELEKTRONIK</h2>
        <div class="doc-number">Nomor: {{ $reportNumber }}</div>
    </div>

    <!-- NARASI PEMBUKA -->
    <p class="justified">
        Pada hari ini, <strong>{{ now()->translatedFormat('l') }}</strong> tanggal <strong>{{ now()->translatedFormat('d F Y') }}</strong>, bertempat di Pusat Kendali Pemilihan Suara Elektronik (E-Voting System), telah dilaksanakan rapat pleno rekapitulasi penghitungan perolehan suara sah untuk agenda pemilihan:
    </p>

    <table style="width: 100%; font-size: 10.5pt; margin: 6px 0 12px 20px;">
        <tr>
            <td style="width: 180px;"><strong>Nama Agenda Pemilihan</strong></td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $event->title }}</strong></td>
        </tr>
        <tr>
            <td><strong>Kategori Kegiatan</strong></td>
            <td>:</td>
            <td>{{ $event->category }}</td>
        </tr>
        <tr>
            <td><strong>Target Hak Pilih</strong></td>
            <td>:</td>
            <td>{{ $event->target_scope_label }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Pelaksanaan</strong></td>
            <td>:</td>
            <td>{{ $event->created_at->format('d/m/Y H:i') }} s.d. {{ now()->format('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Status Agenda</strong></td>
            <td>:</td>
            <td>{{ $event->status === 'CLOSED' ? 'FINAL / DITUTUP' : 'SELESAI PEMUNGUTAN SUARA' }}</td>
        </tr>
    </table>

    <!-- I. DATA PARTISIPASI PEMILIH -->
    <div class="font-bold" style="margin-top: 14px;">I. DATA REKAPITULASI PENGGUNA HAK PILIH (DPT)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Uraian Rekapitulasi Suara</th>
                <th style="width: 140px;">Jumlah</th>
                <th style="width: 120px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Jumlah Daftar Pemilih Tetap (DPT) Terdaftar Aktif</td>
                <td class="text-center font-bold">{{ $stats['totalDPT'] }} Orang</td>
                <td class="text-center">100% Hak Pilih</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Jumlah Pemilih yang Menggunakan Hak Suara (Suara Sah)</td>
                <td class="text-center font-bold">{{ $stats['totalVotes'] }} Suara</td>
                <td class="text-center">{{ $stats['turnoutRate'] }}% Partisipasi</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Jumlah Pemilih yang Tidak Menggunakan Hak Suara</td>
                <td class="text-center font-bold">{{ $stats['nonVoters'] }} Orang</td>
                <td class="text-center">{{ round(100 - $stats['turnoutRate'], 1) }}% Sisa</td>
            </tr>
        </tbody>
    </table>

    <!-- II. HASIL PEROLEHAN SUARA PASLON -->
    <div class="font-bold" style="margin-top: 14px;">II. HASIL PEROLEHAN SUARA PASANGAN CALON</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50px;">No Urut</th>
                <th>Nama Pasangan Calon (Ketua & Wakil)</th>
                <th style="width: 120px;">Perolehan Suara</th>
                <th style="width: 100px;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['candidateStats'] as $cand)
                <tr>
                    <td class="text-center font-bold">#{{ $cand['number'] }}</td>
                    <td>
                        <strong>{{ $cand['chairman'] }}</strong>
                        @if($cand['vice_chairman'])
                            <br><small style="color: #4b5563;">Wakil: {{ $cand['vice_chairman'] }}</small>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $cand['votes'] }} Suara</td>
                    <td class="text-center font-bold">{{ $cand['percentage'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PENETAPAN PEMENANG -->
    @if($stats['winner'])
        <div class="winner-box">
            <strong>PENETAPAN HASIL:</strong> Berdasarkan rekapitulasi perolehan suara sah di atas, Pasangan Calon dengan <strong>Nomor Urut #{{ $stats['winner']->candidate_number }}</strong> atas nama <strong>{{ $stats['winner']->display_name }}</strong> dengan perolehan <strong>{{ $stats['winner']->votes_count }} suara ({{ $stats['candidateStats'][$stats['winner']->candidate_number - 1]['percentage'] ?? 0 }}%)</strong> secara sah dan meyakinkan ditetapkan sebagai pasangan terpilih.
        </div>
    @endif

    <p class="justified">
        Demikian Berita Acara ini dibuat dengan sebenarnya, ditandatangani oleh Panitia Pelaksana dan Saksi-Saksi yang bertugas, serta disahkan untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    Saksi I Pemilihan,
                    <div class="sign-space"></div>
                    <strong>( .................................................. )</strong><br>
                    <small>Perwakilan Siswa / MPK</small>
                </td>
                <td>
                    Ketua Panitia Pemilihan,
                    <div class="sign-space"></div>
                    <strong>( {{ auth()->user()->nama ?? 'Ketua Panitia E-Voting' }} )</strong><br>
                    <small>NIP/NISN: Panitia Pelaksana</small>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 25px;">
                    Mengetahui,<br>
                    <strong>Kepala Sekolah / Pembina Kesiswaan</strong>
                    <div class="sign-space"></div>
                    <strong>( .................................................. )</strong><br>
                    <small>NIP. ......................................................</small>
                </td>
            </tr>
        </table>
    </div>

    <!-- DIGITAL VERIFICATION FOOTPRINT -->
    <div class="stamp-box">
        <div>[E-VOTING CRYPTOGRAPHIC VERIFICATION FOOTPRINT]</div>
        <div>SHA-256 DIGITAL DIGEST : {{ $integrityHash }}</div>
        <div>GENERATED TIMESTAMP   : {{ $generatedAt }} WIB • AUDIT VERIFIED SYSTEM</div>
    </div>
</body>
</html>
