@extends('layouts.app', ['title' => 'Monitoring Suara: ' . $event->title])

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
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ $event->category_label }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                <span>Monitoring Perolehan Suara</span>
                @if($event->isActive())
                    <span
                        x-show="autoPoll"
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live</span>
                    </span>
                    <span
                        x-show="!autoPoll"
                        x-cloak
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 border border-slate-200 text-slate-700"
                    >
                        <span>Selesai</span>
                    </span>
                @elseif($event->status === 'PUBLISHED' && $event->isBeforeStartTime())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Menunggu Jadwal</span>
                    </span>
                @elseif($event->status === 'DRAFT')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Draft</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 border border-slate-200 text-slate-700">
                        <span>Selesai</span>
                    </span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 mt-1.5 font-medium leading-relaxed">
                Agenda: <strong class="text-slate-800">{{ $event->title }}</strong> &bull; Pembaruan Terakhir: <span class="font-mono font-bold tabular-nums text-slate-700" x-text="lastUpdated">{{ now()->format('H:i:s') }}</span>
            </p>
        </div>

        <!-- Action Tools with Consistent Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Export CSV -->
            <a
                href="{{ route('events.report.csv', $event) }}"
                class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 flex items-center shadow-xs transition-colors cursor-pointer"
            >
                <span>Ekspor CSV</span>
            </a>

            <!-- Berita Acara PDF -->
            <a
                href="{{ route('events.report.pdf', $event) }}"
                target="_blank"
                class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs flex items-center shadow-xs border border-slate-900 transition-colors cursor-pointer"
            >
                <span>Cetak Berita Acara (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Daftar Pemilih"
            :value="$stats['totalDPT']"
            x-value="stats.totalDPT"
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
            x-value="stats.totalVotes"
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
            x-value="stats.turnoutRate + '%'"
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
            x-value="stats.nonVoters"
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
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Perolehan Suara Paslon</h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                        <span x-text="autoPoll ? 'Real-time' : 'Hasil Akhir'">{{ $event->isActive() ? 'Real-time' : 'Hasil Akhir' }}</span>
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
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Partisipasi Pemilih</h3>
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
                    <span class="block font-bold tabular-nums text-slate-900 text-base" x-text="stats.totalVotes">{{ $stats['totalVotes'] }}</span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                        <span class="text-slate-500 font-medium">Belum Memilih</span>
                    </div>
                    <span class="block font-bold tabular-nums text-slate-900 text-base" x-text="stats.nonVoters">{{ $stats['nonVoters'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Detailed Cards Grid -->
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                Rincian Perolehan Paslon
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="candidate in stats.candidateStats" :key="candidate.id">
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:border-slate-300 transition-colors relative overflow-hidden flex flex-col justify-between">
                    <!-- Top Ribbon -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-9 h-9 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                                #<span x-text="candidate.number"></span>
                            </span>
                            <div class="text-right">
                                <span class="text-2xl font-bold tabular-nums text-slate-900" x-text="candidate.percentage + '%'"></span>
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
                            <span class="text-slate-900 font-mono font-bold tabular-nums" x-text="candidate.votes + ' Suara'"></span>
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
    let barChart = null;
    let doughnutChart = null;

    return {
        eventId: eventId,
        stats: initialStats,
        lastUpdated: '{{ now()->format('H:i:s') }}',
        autoPoll: {{ $event->isActive() ? 'true' : 'false' }},
        pollInterval: null,

        initCharts() {
            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif";
                Chart.defaults.color = '#64748b';
                Chart.defaults.font.size = 11;
            }
            this.setupBarChart();
            this.setupDoughnutChart();
            if (this.autoPoll) {
                this.startPolling();
            }
        },

        setupBarChart() {
            const el = document.getElementById('barChartCanvas');
            if (!el || typeof Chart === 'undefined') return;

            const existingChart = Chart.getChart(el);
            if (existingChart) {
                existingChart.destroy();
            }

            const ctx = el.getContext('2d');
            const candidates = (this.stats && Array.isArray(this.stats.candidateStats)) ? this.stats.candidateStats : [];
            const labels = candidates.map(c => '#' + c.number + ' ' + (c.chairman || c.display_name));
            const data = candidates.map(c => c.votes);

            barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Suara Sah',
                        data: data,
                        backgroundColor: '#1e293b',
                        hoverBackgroundColor: '#0f172a',
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
            if (!el || typeof Chart === 'undefined') return;

            const existingChart = Chart.getChart(el);
            if (existingChart) {
                existingChart.destroy();
            }

            const ctx = el.getContext('2d');
            doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Memilih', 'Belum Memilih'],
                    datasets: [{
                        data: [this.stats ? this.stats.totalVotes : 0, this.stats ? this.stats.nonVoters : 0],
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
            const bar = barChart || (typeof Chart !== 'undefined' ? Chart.getChart('barChartCanvas') : null);
            if (bar && this.stats && Array.isArray(this.stats.candidateStats)) {
                bar.data.labels = this.stats.candidateStats.map(c => '#' + c.number + ' ' + (c.chairman || c.display_name));
                bar.data.datasets[0].data = this.stats.candidateStats.map(c => c.votes);
                bar.data.datasets[0].backgroundColor = '#1e293b';
                bar.data.datasets[0].hoverBackgroundColor = '#0f172a';
                bar.update();
            }

            const doughnut = doughnutChart || (typeof Chart !== 'undefined' ? Chart.getChart('doughnutChartCanvas') : null);
            if (doughnut && this.stats) {
                doughnut.data.datasets[0].data = [this.stats.totalVotes, this.stats.nonVoters];
                doughnut.update();
            }
        },

        fetchLatestStats() {
            if (typeof axios === 'undefined') return;
            axios.get('{{ route('events.live-stats', $event) }}')
                .then(response => {
                    const data = response.data;
                    this.stats.totalDPT = data.total_dpt;
                    this.stats.totalVotes = data.total_votes;
                    this.stats.turnoutRate = data.turnout_rate;
                    this.stats.nonVoters = data.non_voters;
                    this.stats.candidateStats = data.candidates;
                    this.lastUpdated = data.timestamp;
                    this.updateCharts();

                    if (data.status === 'CLOSED' || !data.is_active) {
                        this.autoPoll = false;
                        this.stopPolling();
                    }
                })
                .catch(err => {
                    console.warn('Gagal memperbarui data live stats:', err);
                    if (err && err.response && [401, 403, 404].includes(err.response.status)) {
                        this.autoPoll = false;
                        this.stopPolling();
                    }
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
