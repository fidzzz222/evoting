<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    // halaman live monitoring hasil pemilihan
    public function show(Event $event)
    {
        $this->authorizeEventAccess($event);
        $event->syncDynamicStatus();

        $stats = $this->calculateElectionStats($event);

        return view('monitoring.show', compact('event', 'stats'));
    }

    // endpoint data real-time untuk polling chart
    public function liveStats(Event $event)
    {
        $this->authorizeEventAccess($event);
        $event->syncDynamicStatus();

        $stats = $this->calculateElectionStats($event);

        return response()->json([
            'status' => $event->status,
            'is_active' => $event->isActive(),
            'total_dpt' => $stats['totalDPT'],
            'total_votes' => $stats['totalVotes'],
            'turnout_rate' => $stats['turnoutRate'],
            'non_voters' => $stats['nonVoters'],
            'candidates' => $stats['candidateStats'],
            'leader' => $stats['leader'] ? [
                'number' => $stats['leader']->candidate_number,
                'name' => $stats['leader']->display_name,
                'votes' => (int) ($stats['leader']->votes_count ?? 0),
            ] : null,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    // unduh berita acara resmi format pdf
    public function downloadPdf(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->load('creator');

        $stats = $this->calculateElectionStats($event);

        // hash integritas digital untuk keabsahan laporan
        $rawVerification = $event->id . '|' . $stats['totalVotes'] . '|' . ($stats['leader']->id ?? 0) . '|' . config('app.key');
        $integrityHash = hash('sha256', $rawVerification);

        $data = [
            'event' => $event,
            'stats' => $stats,
            'reportNumber' => 'BA-EVT/' . date('Y') . '/' . str_pad($event->id, 4, '0', STR_PAD_LEFT),
            'generatedAt' => now()->translatedFormat('d F Y H:i:s'),
            'integrityHash' => strtoupper(substr($integrityHash, 0, 32)),
        ];

        $pdf = Pdf::loadView('reports.berita-acara', $data)
            ->setPaper('a4', 'portrait');

        $safeTitle = Str::slug($event->title) ?: 'event-' . $event->id;
        $fileName = 'Berita-Acara-' . $safeTitle . '.pdf';

        return $pdf->stream($fileName);
    }

    // ekspor rekapitulasi suara ke format csv
    public function exportCsv(Event $event): StreamedResponse
    {
        $this->authorizeEventAccess($event);

        $stats = $this->calculateElectionStats($event);
        $safeTitle = Str::slug($event->title) ?: 'event-' . $event->id;
        $fileName = 'Rekap-Suara-' . $safeTitle . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($event, $stats) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // header info agenda
            fputcsv($output, ['REKAPITULASI HASIL PEMILIHAN ELEKTRONIK']);
            fputcsv($output, ['Agenda Pemilihan', $event->title]);
            fputcsv($output, ['Kategori', $event->category]);
            fputcsv($output, ['Target Hak Pilih', $event->target_scope_label]);
            fputcsv($output, ['Status', $event->status]);
            fputcsv($output, ['Waktu Ekspor', now()->format('Y-m-d H:i:s')]);
            fputcsv($output, []);

            // data partisipasi
            fputcsv($output, ['STATISTIK PARTISIPASI']);
            fputcsv($output, ['Total DPT Berhak', $stats['totalDPT']]);
            fputcsv($output, ['Total Suara Sah Masuk', $stats['totalVotes']]);
            fputcsv($output, ['Belum Menggunakan Hak Suara', $stats['nonVoters']]);
            fputcsv($output, ['Tingkat Partisipasi (%)', $stats['turnoutRate'] . '%']);
            fputcsv($output, []);

            // tabel perolehan suara paslon
            fputcsv($output, ['HASIL PEROLEHAN SUARA PASANGAN CALON']);
            fputcsv($output, ['No Urut', 'Calon Ketua', 'Calon Wakil', 'Perolehan Suara', 'Persentase (%)']);

            foreach ($stats['candidateStats'] as $cand) {
                fputcsv($output, [
                    $cand['number'],
                    $cand['chairman'],
                    $cand['vice_chairman'] ?? '-',
                    $cand['votes'],
                    $cand['percentage'] . '%',
                ]);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    // helper perhitungan statistik pemilihan
    private function calculateElectionStats(Event $event): array
    {
        return $event->getElectionStats();
    }

    // validasi hak akses user terhadap agenda
    protected function authorizeEventAccess(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk memantau agenda pemilihan ini.');
        }
    }
}
