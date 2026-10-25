<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\User;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for school presentation demo.
     */
    public function run(): void
    {
        // 1. Seed Administrator and Petugas
        $superAdmin = User::create([
            'nama' => 'Administrator Utama',
            'email' => 'admin@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $petugas1 = User::create([
            'nama' => 'Budi Santoso (Panitia OSIS)',
            'email' => 'petugas@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $petugas2 = User::create([
            'nama' => 'Siti Rahma (Panitia MPK)',
            'email' => 'petugas2@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        // 2. Seed Master DPT (Siswa & Guru - Passwordless: Auth via NISN/NIP + Kode Bilik Suara)
        $votersData = [
            // Siswa
            ['identifier' => '102401', 'nama' => 'Aditya Pratama', 'kelas' => 'XII IPA 1', 'type' => 'SISWA'],
            ['identifier' => '102402', 'nama' => 'Bella Anatasya', 'kelas' => 'XII IPA 1', 'type' => 'SISWA'],
            ['identifier' => '102403', 'nama' => 'Citra Lestari', 'kelas' => 'XII IPS 2', 'type' => 'SISWA'],
            ['identifier' => '102404', 'nama' => 'Dwi Handayani', 'kelas' => 'XI IPA 2', 'type' => 'SISWA'],
            ['identifier' => '102405', 'nama' => 'Eko Prasetyo', 'kelas' => 'XI IPS 1', 'type' => 'SISWA'],
            ['identifier' => '102406', 'nama' => 'Farhan Maulana', 'kelas' => 'X-1', 'type' => 'SISWA'],
            ['identifier' => '102407', 'nama' => 'Gita Gutawa Putri', 'kelas' => 'X-2', 'type' => 'SISWA'],
            ['identifier' => '102408', 'nama' => 'Hendra Saputra', 'kelas' => 'X-3', 'type' => 'SISWA'],
            ['identifier' => '102409', 'nama' => 'Indah Permatasari', 'kelas' => 'XI IPA 1', 'type' => 'SISWA'],
            ['identifier' => '102410', 'nama' => 'Joko Widodo Putra', 'kelas' => 'XII IPS 1', 'type' => 'SISWA'],

            // Guru
            ['identifier' => '19850101', 'nama' => 'Drs. H. Mulyono, M.Pd.', 'kelas' => 'Dewan Guru', 'type' => 'GURU'],
            ['identifier' => '19870202', 'nama' => 'Sri Wahyuni, S.Pd.', 'kelas' => 'Dewan Guru', 'type' => 'GURU'],
            ['identifier' => '19900303', 'nama' => 'Ahmad Subandi, S.Kom.', 'kelas' => 'Dewan Guru', 'type' => 'GURU'],
        ];

        $voters = [];
        foreach ($votersData as $vData) {
            $voters[] = Voter::create(array_merge($vData, ['is_active' => true]));
        }

        // 3. Seed Main Election Event (Pemilihan Ketua OSIS)
        $now = Carbon::now();
        $eventOsis = Event::create([
            'created_by_user_id' => $superAdmin->id,
            'title' => 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027',
            'category' => 'KETUA_OSIS',
            'description' => 'Gunakan hak suara Anda untuk memilih pemimpin OSIS masa depan SMA Negeri 1 yang amanah, kreatif, dan inspiratif. Suara Anda menentukan masa depan sekolah!',
            'room_code' => 'OSIS26',
            'invite_token' => 'PANITIA-OSIS-2026',
            'start_time' => $now->copy()->subHours(2),
            'end_time' => $now->copy()->addHours(6),
            'status' => 'PUBLISHED',
        ]);

        // Attach Petugas as officer to event
        $eventOsis->officers()->attach($petugas1->id);
        $eventOsis->officers()->attach($petugas2->id);

        // 4. Seed Candidates for Event OSIS
        $candidate1 = Candidate::create([
            'event_id' => $eventOsis->id,
            'candidate_number' => 1,
            'name' => 'Ahmad Fauzi & Nabila Putri',
            'vision' => 'Mewujudkan OSIS SMA Negeri 1 yang aktif, berintegritas, berwawasan global, dan inklusif bagi seluruh minat bakat siswa.',
            'mission' => "1. Menyelenggarakan kegiatan ekstrakurikuler yang inovatif dan berbasis teknologi.\n2. Mengoptimalkan wadah aspirasi siswa secara transparan dan responsif.\n3. Menumbuhkan kepedulian sosial dan kepemimpinan berkarakter.",
            'description' => "Paslon 01 - Calon Ketua & Wakil Ketua OSIS (Kelas XI MIPA 1 & XI MIPA 2)",
        ]);

        $candidate2 = Candidate::create([
            'event_id' => $eventOsis->id,
            'candidate_number' => 2,
            'name' => 'Rizky Pratama & Jessica Chandra',
            'vision' => 'Menjadikan OSIS sebagai wadah kolaborasi sinergis antar kelas, menjunjung sportivitas, dan prestasi akademik maupun non-akademik.',
            'mission' => "1. Menjembatani aspirasi siswa dengan pihak sekolah secara harmonis.\n2. Menggalakkan program peduli lingkungan dan kebersihan sekolah (Green School).\n3. Mendukung kompetisi antarkelas (Classmeeting) yang lebih variatif.",
            'description' => "Paslon 02 - Calon Ketua & Wakil Ketua OSIS (Kelas XI IPS 1 & XI IPS 2)",
        ]);

        $candidate3 = Candidate::create([
            'event_id' => $eventOsis->id,
            'candidate_number' => 3,
            'name' => 'Dimas Wicaksono & Salma Aulia',
            'vision' => 'Terwujudnya lingkungan sekolah yang kreatif, harmonis, menjunjung tinggi nilai gotong royong dan akhlak mulia.',
            'mission' => "1. Meningkatkan kegiatan keagamaan dan toleransi antarsiswa.\n2. Mengembangkan wirausaha muda siswa melalui program koperasi mandiri.\n3. Memberikan ruang seluas-luasnya untuk apresiasi seni budaya.",
            'description' => "Paslon 03 - Calon Ketua & Wakil Ketua OSIS (Kelas XI MIPA 3 & XI IPS 1)",
        ]);

        // 5. Seed Event Participations & Some Initial Anonymous Votes (for demo charts)
        // Let first 6 voters vote already, while remaining 7 have not voted yet!
        $votesToCast = [
            $candidate1->id,
            $candidate1->id,
            $candidate2->id,
            $candidate2->id,
            $candidate2->id,
            $candidate3->id,
        ];

        foreach ($voters as $index => $voter) {
            $hasVoted = $index < count($votesToCast);
            $votedAt = $hasVoted ? $now->copy()->subMinutes(60 - ($index * 8)) : null;

            EventParticipation::create([
                'event_id' => $eventOsis->id,
                'voter_id' => $voter->id,
                'has_voted' => $hasVoted,
                'voted_at' => $votedAt,
            ]);

            if ($hasVoted) {
                // Anonymous vote
                Vote::create([
                    'event_id' => $eventOsis->id,
                    'candidate_id' => $votesToCast[$index],
                    'voted_at' => $votedAt,
                ]);
            }
        }

        // 6. Seed Scheduled/Draft Event (MPK)
        $eventMpk = Event::create([
            'created_by_user_id' => $superAdmin->id,
            'title' => 'Pemilihan Ketua Majelis Perwakilan Kelas (MPK)',
            'category' => 'KETUA_MPK',
            'description' => 'Musyawarah pemilihan pimpinan perwakilan kelas untuk memonitoring aspirasi legislatif siswa.',
            'room_code' => 'MPK2026',
            'invite_token' => 'INVITE-MPK-2026',
            'start_time' => $now->copy()->addDays(2),
            'end_time' => $now->copy()->addDays(2)->addHours(8),
            'status' => 'DRAFT',
        ]);

        $eventMpk->officers()->attach($petugas2->id);

        // 7. Seed Audit Logs
        AuditLog::create([
            'user_type' => 'USER',
            'user_id' => $superAdmin->id,
            'user_name' => $superAdmin->nama,
            'user_role' => $superAdmin->role,
            'action' => 'INIT_SYSTEM',
            'description' => 'Sistem E-Voting berhasil diinisialisasi untuk pemilihan sekolah.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        AuditLog::create([
            'user_type' => 'USER',
            'user_id' => $superAdmin->id,
            'user_name' => $superAdmin->nama,
            'user_role' => $superAdmin->role,
            'action' => 'CREATE_EVENT',
            'description' => "Event '{$eventOsis->title}' dibuat dan dipublikasikan.",
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => ['event_id' => $eventOsis->id],
        ]);
    }
}

