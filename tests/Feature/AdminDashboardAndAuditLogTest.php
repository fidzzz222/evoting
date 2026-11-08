<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardAndAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $petugas;
    protected Voter $voter;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'nama' => 'Super Administrator',
            'email' => 'admin@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $this->petugas = User::create([
            'nama' => 'Panitia Pemilihan',
            'email' => 'panitia@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $this->voter = Voter::create([
            'nama' => 'Ahmad Siswa',
            'identifier' => '102407',
            'type' => 'SISWA',
            'kelas' => 'XII RPL 1',
            'is_active' => true,
        ]);

        $this->event = Event::create([
            'title' => 'Pemilihan Ketua OSIS 2025/2026',
            'room_code' => 'OSIS25',
            'category' => 'OSIS',
            'description' => 'Pemilihan ketua dan wakil ketua OSIS',
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->superAdmin->id,
        ]);

        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Fadhil & Zidan',
            'description' => 'Paslon 1',
        ]);

        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Rian & Dimas',
            'description' => 'Paslon 2',
        ]);
    }

    public function test_admin_login_creates_audit_log_and_redirects_to_dashboard(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@evoting.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin, 'web');

        // Verify audit log exists
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_PANITIA',
            'user_id' => $this->superAdmin->id,
            'user_type' => 'USER',
        ]);

        // Follow redirect to dashboard and assert 200 OK without RelationNotFoundException
        $dashboardResponse = $this->actingAs($this->superAdmin, 'web')
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Selamat Datang, Super Administrator!');
        $dashboardResponse->assertSee('Super Administrator');
        $dashboardResponse->assertSee('OSIS25');
    }

    public function test_dashboard_renders_cleanly_without_audit_logs_and_global_dpt_cards(): void
    {
        // Create mixed audit logs
        AuditLog::log('TEST_USER_ACTION', 'User melakukan modifikasi', null, $this->superAdmin);
        AuditLog::log('TEST_VOTER_ACTION', 'Pemilih masuk ke bilik', null, $this->voter);
        AuditLog::log('SYSTEM_BACKGROUND', 'Sistem melakukan sinkronisasi otomatis', null, null);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // Dashboard does not show audit log table or global DPT / voice cards
        $response->assertDontSee('Log Aktivitas & Audit Trail Keamanan');
        $response->assertDontSee('Daftar Pemilih Tetap');
        $response->assertDontSee('Total Suara Masuk');

        // Instead, dashboard shows 4 lifecycle cards
        $response->assertSee('Total Agenda');
        $response->assertSee('Bilik Suara Aktif');
        $response->assertSee('Draft');
        $response->assertSee('Pemilihan Selesai');
        $response->assertDontSee('Panduan Operasional Pemilihan Mandiri');
    }

    public function test_panitia_navbar_does_not_contain_data_pemilih_while_admin_does(): void
    {
        // Panitia sidebar must NOT contain Data Pemilih
        $panitiaResponse = $this->actingAs($this->petugas, 'web')
            ->get(route('admin.dashboard'));

        $panitiaResponse->assertStatus(200);
        $panitiaResponse->assertDontSee('Data Pemilih');
        $panitiaResponse->assertSee('Panel Panitia');

        // Super admin sidebar MUST contain Data Pemilih
        $adminResponse = $this->actingAs($this->superAdmin, 'web')
            ->get(route('admin.dashboard'));

        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Data Pemilih');
        $adminResponse->assertSee('Panel Administrator');
    }

    public function test_panitia_can_only_see_and_manage_their_own_created_events(): void
    {
        $panitiaLain = User::create([
            'nama' => 'Panitia Lain',
            'email' => 'panitia.lain@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $eventPanitiaLain = Event::create([
            'title' => 'Pemilihan Dewan Penggalang Pramuka',
            'room_code' => 'PRAM26',
            'category' => 'EKSTRAKURIKULER',
            'status' => 'PUBLISHED',
            'created_by_user_id' => $panitiaLain->id,
        ]);

        $eventPanitiaSaya = Event::create([
            'title' => 'Pemilihan Ketua PMR Wira',
            'room_code' => 'PMRW26',
            'category' => 'EKSTRAKURIKULER',
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->petugas->id,
        ]);

        // Dashboard for $this->petugas must only show their own event
        $dashboardResponse = $this->actingAs($this->petugas, 'web')
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Pemilihan Ketua PMR Wira');
        $dashboardResponse->assertSee('PMRW26');
        $dashboardResponse->assertDontSee('Pemilihan Dewan Penggalang Pramuka');
        $dashboardResponse->assertDontSee('PRAM26');

        // Events index for $this->petugas must also only list their own event
        $eventsIndexResponse = $this->actingAs($this->petugas, 'web')
            ->get(route('events.index'));

        $eventsIndexResponse->assertStatus(200);
        $eventsIndexResponse->assertSee('PMRW26');
        $eventsIndexResponse->assertDontSee('PRAM26');

        // Attempting to manage or monitor other panitia's event must return 403 Forbidden
        $this->actingAs($this->petugas, 'web')
            ->get(route('events.show', $eventPanitiaLain))
            ->assertStatus(403);

        $this->actingAs($this->petugas, 'web')
            ->get(route('events.edit', $eventPanitiaLain))
            ->assertStatus(403);

        $this->actingAs($this->petugas, 'web')
            ->get(route('events.monitoring', $eventPanitiaLain))
            ->assertStatus(403);

        $this->actingAs($this->petugas, 'web')
            ->getJson(route('events.live-stats', $eventPanitiaLain))
            ->assertStatus(403);

        $this->actingAs($this->petugas, 'web')
            ->get(route('events.report.pdf', $eventPanitiaLain))
            ->assertStatus(403);

        $this->actingAs($this->petugas, 'web')
            ->get(route('events.report.csv', $eventPanitiaLain))
            ->assertStatus(403);
    }

    public function test_audit_logs_index_page_loads_with_all_actor_types(): void
    {
        AuditLog::log('USER_LOGIN', 'Panitia login sistem', null, $this->petugas);
        AuditLog::log('VOTER_VOTE', 'Pemilih memberikan suara sah', null, $this->voter);
        AuditLog::log('SYSTEM_CLEAR', 'Cache aplikasi dibersihkan', null, null);

        $response = $this->actingAs($this->superAdmin, 'web')
            ->get(route('admin.logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail & Log', false);
        $response->assertSee('Panitia Pemilihan');
        $response->assertSee('Ahmad Siswa');
    }

    public function test_audit_logs_can_be_cleared_and_pruned(): void
    {
        AuditLog::log('OLD_ACTION', 'Aksi lama 40 hari lalu');
        // Backdate one log
        AuditLog::where('action', 'OLD_ACTION')->update(['created_at' => now()->subDays(40)]);

        $pruneResponse = $this->actingAs($this->superAdmin, 'web')
            ->post(route('admin.logs.prune'), ['days' => 30]);

        $pruneResponse->assertRedirect();
        $this->assertDatabaseMissing('audit_logs', ['action' => 'OLD_ACTION']);

        $cacheResponse = $this->actingAs($this->superAdmin, 'web')
            ->post(route('admin.logs.clear-cache'));

        $cacheResponse->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'SYSTEM_CACHE_CLEARED']);
    }

    public function test_petugas_can_login_and_access_dashboard(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'panitia@evoting.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->petugas, 'web');

        $dashboardResponse = $this->actingAs($this->petugas, 'web')
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Panitia Pemilihan');
    }
}
