<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Voter;
use App\Services\VoterImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoterManagementController extends Controller
{
    /**
     * Tampilkan data master DPT (Daftar Pemilih Tetap).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $kelas = $request->query('kelas');
        $status = $request->query('status');

        $voters = Voter::query()
            ->when($search, function ($query, $s) {
                $query->where(function ($q) use ($s) {
                    $q->where('nama', 'like', "%{$s}%")
                      ->orWhere('identifier', 'like', "%{$s}%");
                });
            })
            ->when($type, function ($query, $t) {
                $query->where('type', $t);
            })
            ->when($kelas, function ($query, $k) {
                $query->where('kelas', $k);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', (bool) $status);
            })
            ->orderBy('type', 'asc')
            ->orderBy('kelas', 'asc')
            ->orderBy('nama', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Daftar kelas unik untuk dropdown filter
        $classList = Voter::select('kelas')->whereNotNull('kelas')->distinct()->pluck('kelas');

        $totalDPT = Voter::count();
        $totalSiswa = Voter::where('type', 'SISWA')->count();
        $totalGuru = Voter::where('type', 'GURU')->count();
        $activeDPT = Voter::where('is_active', true)->count();

        return view('admin.voters.index', compact(
            'voters', 'search', 'type', 'kelas', 'status', 'classList',
            'totalDPT', 'totalSiswa', 'totalGuru', 'activeDPT'
        ));
    }

    /**
     * Form tambah pemilih manual.
     */
    public function create()
    {
        return view('admin.voters.create');
    }

    /**
     * Simpan pemilih manual baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => 'required|string|max:50|unique:voters,identifier',
            'voter_type' => 'required|in:SISWA,GURU',
            'kelas' => 'nullable|string|max:50',
        ]);

        $voter = Voter::create([
            'nama' => $validated['nama'],
            'identifier' => $validated['nisn'],
            'type' => $validated['voter_type'],
            'kelas' => $validated['kelas'],
            'is_active' => true,
        ]);

        AuditLog::log('VOTER_CREATED', "Menambahkan DPT: {$voter->nama} ({$voter->identifier})");

        return redirect()->route('admin.voters.index')
            ->with('success', "Data pemilih {$voter->nama} berhasil didaftarkan.");
    }

    /**
     * Form edit pemilih.
     */
    public function edit(Voter $voter)
    {
        return view('admin.voters.edit', compact('voter'));
    }

    /**
     * Perbarui data pemilih.
     */
    public function update(Request $request, Voter $voter)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => 'required|string|max:50|unique:voters,identifier,' . $voter->id,
            'voter_type' => 'required|in:SISWA,GURU',
            'kelas' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'nama' => $validated['nama'],
            'identifier' => $validated['nisn'],
            'type' => $validated['voter_type'],
            'kelas' => $validated['kelas'],
            'is_active' => $request->boolean('is_active'),
        ];

        $voter->update($data);

        AuditLog::log('VOTER_UPDATED', "Memperbarui data DPT: {$voter->nama} ({$voter->identifier})");

        return redirect()->route('admin.voters.index')
            ->with('success', "Data pemilih {$voter->nama} berhasil diperbarui.");
    }

    /**
     * Hapus pemilih.
     */
    public function destroy(Voter $voter)
    {
        $nama = $voter->nama;
        $voter->participations()->delete();
        $voter->delete();

        AuditLog::log('VOTER_DELETED', "Menghapus data DPT: {$nama}");

        return redirect()->route('admin.voters.index')
            ->with('success', "Pemilih {$nama} berhasil dihapus.");
    }

    /**
     * Toggle status aktif pemilih.
     */
    public function toggleStatus(Voter $voter)
    {
        $voter->is_active = !$voter->is_active;
        $voter->save();

        $statusText = $voter->is_active ? 'Diaktifkan' : 'Dinonaktifkan';
        AuditLog::log('VOTER_STATUS_TOGGLED', "Status DPT {$voter->nama} diubah menjadi {$statusText}");

        return back()->with('success', "Status pemilih {$voter->nama} berhasil diubah.");
    }

    /**
     * Import massal DPT via file CSV.
     */
    public function importCsv(Request $request, VoterImportService $importService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $result = $importService->importCsvForWeb($request->file('csv_file'));

        return redirect()->route('admin.voters.index')
            ->with('success', "Import DPT selesai: {$result['imported']} baru, {$result['updated']} diperbarui.");
    }

    /**
     * Ekspor seluruh DPT ke format CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $voters = Voter::orderBy('type')->orderBy('kelas')->orderBy('nama')->get();
        $fileName = 'Data-DPT-Evoting-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($voters) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($output, ['No', 'Nama Lengkap', 'NISN / NIP', 'Kategori', 'Kelas', 'Status Aktif']);

            foreach ($voters as $index => $voter) {
                fputcsv($output, [
                    $index + 1,
                    $voter->nama,
                    $voter->identifier,
                    $voter->type,
                    $voter->kelas ?? '-',
                    $voter->is_active ? 'AKTIF' : 'NONAKTIF',
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Unduh file template CSV untuk memudahkan panitia mengisi data DPT sekolah.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $fileName = 'Template-Import-DPT.csv';

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($output, ['nama', 'nisn', 'voter_type', 'kelas']);
            fputcsv($output, ['Ahmad Fauzi', '0061234567', 'SISWA', 'XII IPA 1']);
            fputcsv($output, ['Dewi Lestari, S.Pd.', '198501012010012001', 'GURU', 'DEWAN GURU']);
            fputcsv($output, ['Budi Santoso', '0067654321', 'SISWA', 'XI IPS 2']);

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
