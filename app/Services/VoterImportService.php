<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Voter;
use Illuminate\Http\UploadedFile;

class VoterImportService
{
    // Import atau sinkronisasi DPT dari file CSV format web: nama, identifier/nisn, type, kelas
    public function importCsvForWeb(UploadedFile|string $file): array
    {
        $handle = $this->openCsvFile($file);
        $rowNumber = 0;
        $importedCount = 0;
        $updatedCount = 0;

        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;

                // Lewati baris header jika kolom pertama adalah nama, name, nisn, atau no
                if ($rowNumber === 1 && in_array(strtolower(trim($row[0] ?? '')), ['nama', 'name', 'nisn', 'no'], true)) {
                    continue;
                }

                // Minimal butuh: nama (0), identifier/nisn (1)
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $nama = trim($row[0]);
                $nisn = trim($row[1]);
                $voterType = isset($row[2]) && in_array(strtoupper(trim($row[2])), ['GURU', 'SISWA'], true)
                    ? strtoupper(trim($row[2]))
                    : 'SISWA';
                $kelas = isset($row[3]) && trim($row[3]) !== '' ? trim($row[3]) : null;

                $existing = Voter::where('identifier', $nisn)->first();

                if ($existing) {
                    $existing->update([
                        'nama' => $nama,
                        'type' => $voterType,
                        'kelas' => $kelas,
                        'is_active' => true,
                    ]);
                    $updatedCount++;
                } else {
                    Voter::create([
                        'nama' => $nama,
                        'identifier' => $nisn,
                        'type' => $voterType,
                        'kelas' => $kelas,
                        'is_active' => true,
                    ]);
                    $importedCount++;
                }
            }
        } finally {
            fclose($handle);
        }

        AuditLog::log('DPT_IMPORT_CSV', "Import CSV DPT: {$importedCount} baru, {$updatedCount} diperbarui");

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
        ];
    }

    // Import DPT dari CSV format API: identifier, nama, type
    public function importCsvForApi(UploadedFile|string $file): array
    {
        $handle = $this->openCsvFile($file);
        $imported = 0;
        $errors = [];

        try {
            // Lewati header row pertama
            fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3) {
                    continue;
                }

                $identifier = trim($row[0]);
                $nama = trim($row[1]);
                $type = strtoupper(trim($row[2]));

                if (Voter::where('identifier', $identifier)->exists()) {
                    $errors[] = "Identifier {$identifier} sudah terdaftar.";
                    continue;
                }

                Voter::create([
                    'identifier' => $identifier,
                    'nama' => $nama,
                    'type' => in_array($type, ['SISWA', 'GURU'], true) ? $type : 'SISWA',
                    'is_active' => true,
                ]);

                $imported++;
            }
        } finally {
            fclose($handle);
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    // Import DPT dari batch array data terverifikasi (endpoint API)
    public function importArrayForApi(array $votersData): array
    {
        $imported = 0;
        $errors = [];

        foreach ($votersData as $vData) {
            if (Voter::where('identifier', $vData['identifier'])->exists()) {
                $errors[] = "Identifier {$vData['identifier']} sudah terdaftar.";
                continue;
            }

            Voter::create([
                'identifier' => $vData['identifier'],
                'nama' => $vData['nama'],
                'type' => $vData['type'],
                'is_active' => true,
            ]);

            $imported++;
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    // Buka file CSV dan bersihkan UTF-8 BOM jika ada
    protected function openCsvFile(UploadedFile|string $file)
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Tidak dapat membuka file CSV di path: {$path}");
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        return $handle;
    }
}
