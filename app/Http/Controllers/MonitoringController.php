<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Voter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman live monitoring hasil pemilihan.
     */
    public function show(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->load(['candidates' => function ($query) {
            $query->orderBy('candidate_number', 'asc');
        }]);

        $stats = $this->calculateElectionStats($event);

        return view('monitoring.show', compact('event', 'stats'));
    }

    /**
     * Endpoint API JSON untuk pembaruan data real-time chart via polling Axios.
     */
    public function liveStats(Event $event)
    {
        $this->authorizeEventAccess($event);

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
                'votes' => $stats['leader']->votes_count,
            ] : null,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Unduh / Tampilkan Berita Acara Resmi Format PDF.
     */
    public function downloadPdf(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->load(['candidates' => function ($query) {
            $query->orderBy('candidate_number', 'asc');
        }, 'creator']);

        $stats = $this->calculateElectionStats($event);

        // Buat hash integritas digital untuk keabsahan laporan
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

    /**
     * Ekspor Rekapitulasi Suara ke format CSV untuk Excel.
     */
    public function exportCsv(Event $event): StreamedResponse
    {
        $this->authorizeEventAccess($event);

        $event->load(['candidates' => function ($query) {
            $query->orderBy('candidate_number', 'asc');
        }]);

        $stats = $this->calculateElectionStats($event);
        $safeTitle = Str::slug($event->title) ?: 'event-' . $event->id;
        $fileName = 'Rekap-Suara-' . $safeTitle . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($event, $stats) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Header info agenda
            fputcsv($output, ['REKAPITULASI HASIL PEMILIHAN ELEKTRONIK']);
            fputcsv($output, ['Agenda Pemilihan', $event->title]);
            fputcsv($output, ['Kategori', $event->category]);
            fputcsv($output, ['Target Hak Pilih', $event->target_scope_label]);
            fputcsv($output, ['Status', $event->status]);
            fputcsv($output, ['Waktu Ekspor', now()->format('Y-m-d H:i:s')]);
            fputcsv($output, []);

            // Data partisipasi
            fputcsv($output, ['STATISTIK PARTISIPASI']);
            fputcsv($output, ['Total DPT Berhak', $stats['totalDPT']]);
            fputcsv($output, ['Total Suara Sah Masuk', $stats['totalVotes']]);
            fputcsv($output, ['Belum Menggunakan Hak Suara', $stats['nonVoters']]);
            fputcsv($output, ['Tingkat Partisipasi (%)', $stats['turnoutRate'] . '%']);
            fputcsv($output, []);

            // Tabel Paslon
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

    /**
     * Helper perhitungan statistik pemilihan
     */
    private function calculateElectionStats(Event $event): array
    {
        $totalDPT = $event->eligibleVotersCount();
        $totalVotes = $event->votes()->count();
        $turnoutRate = $totalDPT > 0 ? round(($totalVotes / $totalDPT) * 100, 1) : 0;
        $nonVoters = max(0, $totalDPT - $totalVotes);

        $candidates = $event->candidates;
        $candidateStats = [];
        $leader = null;
        $maxVotes = -1;
        $isTie = false;

        foreach ($candidates as $c) {
            $percentage = $totalVotes > 0 ? round(($c->votes_count / $totalVotes) * 100, 1) : 0;
            
            $candidateStats[] = [
                'id' => $c->id,
                'number' => $c->candidate_number,
                'chairman' => $c->chairman_name,
                'vice_chairman' => $c->vice_chairman_name,
                'display_name' => $c->display_name,
                'photo' => $c->photo ? (str_starts_with($c->photo, 'http') ? $c->photo : asset($c->photo)) : null,
                'votes' => $c->votes_count,
                'percentage' => $percentage,
            ];

            if ($c->votes_count > $maxVotes) {
                $maxVotes = $c->votes_count;
                $leader = $c;
                $isTie = false;
            } elseif ($c->votes_count === $maxVotes && $maxVotes > 0) {
                $isTie = true;
            }
        }

        // Jika seri atau belum ada suara, tandai pemenang
        $winner = ($event->status === 'CLOSED' && !$isTie && $maxVotes > 0) ? $leader : null;

        return [
            'totalDPT' => $totalDPT,
            'totalVotes' => $totalVotes,
            'turnoutRate' => $turnoutRate,
            'nonVoters' => $nonVoters,
            'candidateStats' => $candidateStats,
            'leader' => $leader,
            'winner' => $winner,
            'isTie' => $isTie,
        ];
    }

    /**
     * Authorize user access to this event.
     */
    protected function authorizeEventAccess(Event $event): void
    {
        $user = Auth::user();

        if ($user && $user->isSuperAdmin()) {
            return;
        }

        if (!$user || $event->created_by_user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk memantau agenda pemilihan ini.');
        }
    }
}
