@extends('layouts.app', ['title' => 'Live Monitoring: ' . $event->title])

@section('content')
<div class="space-y-7" x-data="monitoringApp({{ json_encode($event->id) }}, {{ json_encode($stats) }})" x-init="initCharts()">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Detail Agenda</span>
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    {{ $event->category_label }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span>Live Quick Count & Monitoring</span>
                <span 
                    x-show="autoPoll" 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Aktif</span>
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1.5 font-medium leading-relaxed">
                Agenda: <strong class="text-slate-800">{{ $event->title }}</strong> &bull; Pembaruan Terakhir: <span class="font-mono font-bold text-slate-700" x-text="lastUpdated">{{ now()->format('H:i:s') }}</span>
            </p>
        </div>

        <!-- Action Tools with Consistent Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Export CSV -->
            <a 
                href="{{ route('events.report.csv', $event) }}" 
                class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Ekspor CSV</span>
            </a>

            <!-- Berita Acara PDF -->
            <a 
                href="{{ route('events.report.pdf', $event) }}" 
                target="_blank"
                class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs flex items-center gap-2 shadow-xs border border-slate-800 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Cetak Berita Acara (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Pemenang Banner (Jika Pemilihan CLOSED) Solid Agency-Grade -->
    @if($event->status === 'CLOSED' && $stats['winner'])
        <div class="bg-slate-900 rounded-2xl p-6 sm:p-7 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">
                        Hasil Akhir Resmi • Paslon Terpilih
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold mt-1 text-white">
                        Paslon #{{ $stats['winner']->candidate_number }} - {{ $stats['winner']->display_name }}
                    </h2>
                    <p class="text-xs text-slate-300 mt-1 font-medium">
                        Meraih perolehan terbanyak dengan <strong class="text-amber-400">{{ $stats['winner']->votes_count }} suara sah</strong>
                    </p>
                </div>
            </div>
            <a 
                href="{{ route('events.report.pdf', $event) }}" 
                target="_blank"
                class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs transition-colors shadow-xs shrink-0"
            >
                Unduh Lembar Pengesahan PDF &rarr;
            </a>
        </div>
    @endif

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total DPT Terdaftar" 
            :value="$stats['totalDPT']" 
            helper="Daftar Pemilih Tetap aktif"
            color="blue"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Total Suara Masuk" 
            :value="$stats['totalVotes']" 
            helper="Surat suara sah terverifikasi"
            color="emerald"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Tingkat Partisipasi" 
            :value="$stats['turnoutRate'] . '%'" 
            helper="Persentase DPT yang telah memilih"
            color="amber"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card 
            title="Belum Memilih" 
            :value="$stats['nonVoters']" 
            helper="Sisa hak suara yang belum masuk"
            color="rose"
        >
            <x-slot:icon>
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Perbandingan Suara Paslon</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Jumlah suara riil yang diperoleh masing-masing pasangan calon</p>
                    </div>
                    <span class="text-xs font-mono font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                        Real-Time Count
                    </span>
                </div>

                <div class="mt-6 h-72 sm:h-80 relative">
                    <canvas id="barChartCanvas"></canvas>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="pb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Proporsi Partisipasi</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Persentase suara masuk vs sisa hak suara</p>
                </div>

                <div class="mt-6 h-56 sm:h-64 relative flex items-center justify-center">
                    <canvas id="doughnutChartCanvas"></canvas>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-center text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
                        <span class="text-slate-500 font-medium">Sudah Memilih</span>
                    </div>
                    <span class="block font-extrabold text-slate-900 text-base" x-text="stats.totalVotes">{{ $stats['totalVotes'] }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                        <span class="text-slate-500 font-medium">Belum Memilih</span>
                    </div>
                    <span class="block font-extrabold text-slate-900 text-base" x-text="stats.nonVoters">{{ $stats['nonVoters'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Detailed Cards Grid -->
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                Rincian Perolehan Setiap Pasangan Calon
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Statistik perolehan suara detail dan persentase kuorum per kandidat
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="candidate in stats.candidateStats" :key="candidate.id">
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:border-slate-300 transition-colors relative overflow-hidden flex flex-col justify-between">
                    <!-- Top Ribbon -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-9 h-9 rounded-xl bg-slate-900 text-white font-black text-sm flex items-center justify-center shadow-xs">
                                #<span x-text="candidate.number"></span>
                            </span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-slate-900 tabular-nums" x-text="candidate.percentage + '%'"></span>
                            </div>
                        </div>

                        <!-- Names -->
                        <div class="mb-5 space-y-1">
                            <h4 class="text-base font-bold text-slate-900 line-clamp-1" x-text="candidate.display_name"></h4>
                            <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="text-slate-400">Ketua:</span>
                                <span class="text-slate-800 font-semibold" x-text="candidate.chairman"></span>
                            </div>
                            <template x-if="candidate.vice_chairman">
                                <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                    <span class="text-slate-400">Wakil:</span>
                                    <span class="text-slate-800 font-semibold" x-text="candidate.vice_chairman"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Vote Meter -->
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between text-xs mb-2 font-semibold">
                            <span class="text-slate-500">Perolehan Suara</span>
                            <span class="text-slate-900 font-mono font-bold" x-text="candidate.votes + ' Suara'"></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200/50">
                            <div 
                                class="bg-slate-900 h-full rounded-full transition-all duration-500" 
                                :style="'width: ' + candidate.percentage + '%'"
                            ></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function monitoringApp(eventId, initialStats) {
    return {
        eventId: eventId,
        stats: initialStats,
        lastUpdated: '{{ now()->format('H:i:s') }}',
        autoPoll: true,
        pollInterval: null,
        barChart: null,
        doughnutChart: null,

        initCharts() {
            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = "'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif";
                Chart.defaults.color = '#64748b';
                Chart.defaults.font.size = 11;
            }
            this.setupBarChart();
            this.setupDoughnutChart();
            this.startPolling();
        },

        setupBarChart() {
            const el = document.getElementById('barChartCanvas');
            if (!el) return;
            const ctx = el.getContext('2d');
            const labels = this.stats.candidateStats.map(c => '#' + c.number + ' ' + c.chairman);
            const data = this.stats.candidateStats.map(c => c.votes);

            this.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Suara Sah',
                        data: data,
                        backgroundColor: [
                            '#0f172a',
                            '#2563eb',
                            '#475569',
                            '#0d9488',
                            '#64748b',
                            '#334155',
                        ],
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 48,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Suara Sah';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                color: '#64748b',
                                font: { weight: '600' }
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.7)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#334155',
                                font: { weight: '600' }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        },

        setupDoughnutChart() {
            const el = document.getElementById('doughnutChartCanvas');
            if (!el) return;
            const ctx = el.getContext('2d');
            this.doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Memilih', 'Belum Memilih'],
                    datasets: [{
                        data: [this.stats.totalVotes, this.stats.nonVoters],
                        backgroundColor: [
                            '#0f172a',
                            '#e2e8f0',
                        ],
                        borderWidth: 0,
                        hoverOffset: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed + ' Suara';
                                }
                            }
                        }
                    },
                    cutout: '72%'
                }
            });
        },

        updateCharts() {
            if (this.barChart) {
                this.barChart.data.labels = this.stats.candidateStats.map(c => '#' + c.number + ' ' + c.chairman);
                this.barChart.data.datasets[0].data = this.stats.candidateStats.map(c => c.votes);
                this.barChart.update();
            }

            if (this.doughnutChart) {
                this.doughnutChart.data.datasets[0].data = [this.stats.totalVotes, this.stats.nonVoters];
                this.doughnutChart.update();
            }
        },

        fetchLatestStats() {
            axios.get(`/events/${this.eventId}/live-stats`)
                .then(response => {
                    const data = response.data;
                    this.stats.totalDPT = data.total_dpt;
                    this.stats.totalVotes = data.total_votes;
                    this.stats.turnoutRate = data.turnout_rate;
                    this.stats.nonVoters = data.non_voters;
                    this.stats.candidateStats = data.candidates;
                    this.lastUpdated = data.timestamp;
                    this.updateCharts();
                })
                .catch(err => {
                    console.warn('Gagal memperbarui data live stats:', err);
                });
        },

        togglePolling() {
            this.autoPoll = !this.autoPoll;
            if (this.autoPoll) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        },

        startPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            this.pollInterval = setInterval(() => {
                this.fetchLatestStats();
            }, 5000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        }
    };
}
</script>
@endpush
@endsection
