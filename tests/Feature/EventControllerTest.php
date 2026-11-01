<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Super Administrator',
            'email' => 'admin@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $this->event = Event::create([
            'title' => 'Pemilihan Ketua OSIS',
            'room_code' => 'OSIS01',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'description' => 'Pemilihan tahunan OSIS',
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'DRAFT',
            'created_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_view_events_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('events.index'));

        $response->assertStatus(200);
        $response->assertSee('Pemilihan Ketua OSIS');
        $response->assertSee('#OSIS01');
    }

    public function test_admin_can_view_event_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('events.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Agenda Pemilihan');
    }

    public function test_admin_can_store_new_event(): void
    {
        $payload = [
            'title' => 'Pemilihan Ketua Kelas XII RPL 1',
            'category' => 'KELAS',
            'target_scope' => 'KELAS',
            'target_class' => 'XII RPL 1',
            'room_code' => 'KLS12A',
            'description' => 'Pemilihan internal kelas XII RPL 1',
            'start_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(2)->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($this->admin)->post(route('events.store'), $payload);

        $createdEvent = Event::where('room_code', 'KLS12A')->firstOrFail();
        $response->assertRedirect(route('events.candidates.create', $createdEvent));
        $this->assertDatabaseHas('events', [
            'title' => 'Pemilihan Ketua Kelas XII RPL 1',
            'category' => 'KELAS',
            'target_scope' => 'KELAS',
            'target_class' => 'XII RPL 1',
            'room_code' => 'KLS12A',
        ]);
    }

    public function test_admin_can_view_event_show(): void
    {
        $response = $this->actingAs($this->admin)->get(route('events.show', $this->event));

        $response->assertStatus(200);
        $response->assertSee($this->event->title);
        $response->assertSee($this->event->room_code);
    }

    public function test_admin_can_view_event_edit_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('events.edit', $this->event));

        $response->assertStatus(200);
        $response->assertSee('Edit Agenda Pemilihan');
        $response->assertSee($this->event->title);
    }

    public function test_admin_can_update_event(): void
    {
        $payload = [
            'title' => 'Pemilihan Ketua OSIS & MPK Terpadu',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'room_code' => $this->event->room_code,
            'description' => 'Deskripsi yang telah diperbarui',
            'start_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(3)->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($this->admin)->put(route('events.update', $this->event), $payload);

        $response->assertRedirect(route('events.show', $this->event));
        $this->assertDatabaseHas('events', [
            'id' => $this->event->id,
            'title' => 'Pemilihan Ketua OSIS & MPK Terpadu',
            'description' => 'Deskripsi yang telah diperbarui',
        ]);
    }

    public function test_admin_can_update_event_status(): void
    {
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Kandidat 1 & Wakil 1',
            'vision' => 'Visi 1',
            'mission' => 'Misi 1',
        ]);

        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Kandidat 2 & Wakil 2',
            'vision' => 'Visi 2',
            'mission' => 'Misi 2',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('events.status.update', $this->event), [
            'status' => 'PUBLISHED',
        ]);

        $response->assertRedirect();
        $this->assertEquals('PUBLISHED', $this->event->fresh()->status);
    }

    public function test_admin_can_view_monitoring_page(): void
    {
        // Event in DRAFT state
        $response = $this->actingAs($this->admin)->get(route('events.monitoring', $this->event));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Perolehan Suara');
        $response->assertSee('Draft');

        // Event in ACTIVE (PUBLISHED + 2 candidates + in schedule) state
        $this->event->update(['status' => 'PUBLISHED']);
        Candidate::create(['event_id' => $this->event->id, 'candidate_number' => 1, 'name' => 'Paslon 1']);
        Candidate::create(['event_id' => $this->event->id, 'candidate_number' => 2, 'name' => 'Paslon 2']);

        $responseActive = $this->actingAs($this->admin)->get(route('events.monitoring', $this->event));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Live');
        $responseActive->assertSee('Real-time');
        $responseActive->assertSee('autoPoll: true', false);

        // Event in CLOSED state
        $this->event->update(['status' => 'CLOSED']);
        $responseClosed = $this->actingAs($this->admin)->get(route('events.monitoring', $this->event));
        $responseClosed->assertStatus(200);
        $responseClosed->assertSee('Selesai');
        $responseClosed->assertSee('Hasil Akhir');
        $responseClosed->assertSee('autoPoll: false', false);
    }

    public function test_admin_can_delete_event(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('events.destroy', $this->event));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $this->event->id]);
    }

    public function test_store_event_requires_start_time_and_end_time(): void
    {
        $payload = [
            'title' => 'Pemilihan Tanpa Jadwal',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'room_code' => 'NOTIME',
        ];

        $response = $this->actingAs($this->admin)->post(route('events.store'), $payload);

        $response->assertSessionHasErrors(['start_time', 'end_time']);
    }

    public function test_store_event_fails_if_end_time_is_before_start_time(): void
    {
        $payload = [
            'title' => 'Pemilihan Waktu Terbalik',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'room_code' => 'TIMERR',
            'start_time' => Carbon::now()->addDays(2)->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(1)->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($this->admin)->post(route('events.store'), $payload);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_event_auto_closes_when_end_time_has_passed(): void
    {
        $expiredEvent = Event::create([
            'title' => 'Pemilihan Kemarin',
            'room_code' => 'EXPR01',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subHour(),
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->admin->id,
        ]);

        $this->assertEquals('PUBLISHED', $expiredEvent->status);
        $this->assertTrue($expiredEvent->isAfterEndTime());

        // Call syncDynamicStatus
        $updated = $expiredEvent->syncDynamicStatus();
        $this->assertTrue($updated);
        $this->assertEquals('CLOSED', $expiredEvent->fresh()->status);
    }

    public function test_admin_cannot_publish_event_if_end_time_has_passed(): void
    {
        $expiredEvent = Event::create([
            'title' => 'Pemilihan Kadaluarsa',
            'room_code' => 'EXPR02',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subMinutes(30),
            'status' => 'DRAFT',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $expiredEvent->id,
            'candidate_number' => 1,
            'name' => 'Kandidat 1',
            'vision' => 'Visi 1',
            'mission' => 'Misi 1',
        ]);

        Candidate::create([
            'event_id' => $expiredEvent->id,
            'candidate_number' => 2,
            'name' => 'Kandidat 2',
            'vision' => 'Visi 2',
            'mission' => 'Misi 2',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('events.status.update', $expiredEvent), [
            'status' => 'PUBLISHED',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('DRAFT', $expiredEvent->fresh()->status);
    }

    public function test_artisan_events_sync_status_command(): void
    {
        Event::create([
            'title' => 'Pemilihan Berakhir Kemarin',
            'room_code' => 'EXPR03',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subDays(3),
            'end_time' => Carbon::now()->subDay(),
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->admin->id,
        ]);

        $this->artisan('events:sync-status')
            ->expectsOutputToContain('1 agenda ditutup')
            ->assertExitCode(0);
    }

    public function test_event_auto_starts_when_start_time_has_arrived_and_has_two_candidates(): void
    {
        $draftEvent = Event::create([
            'title' => 'Pemilihan Dimulai Hari Ini',
            'room_code' => 'STRT01',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addDay(),
            'status' => 'DRAFT',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $draftEvent->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        Candidate::create([
            'event_id' => $draftEvent->id,
            'candidate_number' => 2,
            'name' => 'Paslon 2',
        ]);

        $this->assertEquals('DRAFT', $draftEvent->status);
        $this->assertTrue($draftEvent->isInScheduledWindow());

        $updated = $draftEvent->syncDynamicStatus();
        $this->assertTrue($updated);
        $this->assertEquals('PUBLISHED', $draftEvent->fresh()->status);
    }

    public function test_event_does_not_auto_start_without_minimum_two_candidates(): void
    {
        $draftEvent = Event::create([
            'title' => 'Pemilihan Belum Cukup Kandidat',
            'room_code' => 'STRT02',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addDay(),
            'status' => 'DRAFT',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $draftEvent->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $this->assertEquals('DRAFT', $draftEvent->status);
        $this->assertTrue($draftEvent->isInScheduledWindow());

        $updated = $draftEvent->syncDynamicStatus();
        $this->assertFalse($updated);
        $this->assertEquals('DRAFT', $draftEvent->fresh()->status);
    }

    public function test_published_event_reverts_to_draft_if_candidates_count_drops_below_two(): void
    {
        $activeEvent = Event::create([
            'title' => 'Pemilihan Aktif Kurang Paslon',
            'room_code' => 'REVT01',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => Carbon::now()->addDay(),
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->admin->id,
        ]);

        // Hanya memiliki 1 paslon padahal status PUBLISHED
        Candidate::create([
            'event_id' => $activeEvent->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $updated = $activeEvent->syncDynamicStatus();
        $this->assertTrue($updated);
        $this->assertEquals('DRAFT', $activeEvent->fresh()->status);
    }

    public function test_event_sync_all_statuses_opens_and_closes_events(): void
    {
        // 1 Event yang harus tutup
        Event::create([
            'title' => 'Pemilihan Kemarin',
            'room_code' => 'CLS001',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subHour(),
            'status' => 'PUBLISHED',
            'created_by_user_id' => $this->admin->id,
        ]);

        // 1 Event yang harus buka (memiliki minimal 2 paslon)
        $openEvent = Event::create([
            'title' => 'Pemilihan Baru Mulai',
            'room_code' => 'OPN001',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subMinute(),
            'end_time' => Carbon::now()->addDays(2),
            'status' => 'DRAFT',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $openEvent->id,
            'candidate_number' => 1,
            'name' => 'Paslon A',
        ]);

        Candidate::create([
            'event_id' => $openEvent->id,
            'candidate_number' => 2,
            'name' => 'Paslon B',
        ]);

        $result = Event::syncAllEventStatuses();

        $this->assertGreaterThanOrEqual(1, $result['closed']);
        $this->assertGreaterThanOrEqual(1, $result['started']);
        $this->assertEquals('CLOSED', Event::where('room_code', 'CLS001')->first()->status);
        $this->assertEquals('PUBLISHED', Event::where('room_code', 'OPN001')->first()->status);
    }

    public function test_super_admins_are_automatically_attached_when_petugas_creates_event_and_can_access(): void
    {
        $secondAdmin = User::create([
            'nama' => 'Admin Dua',
            'email' => 'admin2@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $petugas = User::create([
            'nama' => 'Petugas Satu',
            'email' => 'petugas@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $payload = [
            'title' => 'Pemilihan Dibuat Petugas',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'room_code' => 'PTGS01',
            'description' => 'Event oleh petugas',
            'start_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(2)->format('Y-m-d\TH:i'),
        ];

        $response = $this->actingAs($petugas)->post(route('events.store'), $payload);
        $response->assertRedirect();

        $event = Event::where('room_code', 'PTGS01')->firstOrFail();

        $this->assertEquals($petugas->id, $event->created_by_user_id);
        $this->assertTrue($event->officers->contains('id', $this->admin->id));
        $this->assertTrue($event->officers->contains('id', $secondAdmin->id));

        // Both super admins can view index and find the event
        $indexResponse1 = $this->actingAs($this->admin)->get(route('events.index'));
        $indexResponse1->assertStatus(200);
        $indexResponse1->assertSee('Pemilihan Dibuat Petugas');

        $indexResponse2 = $this->actingAs($secondAdmin)->get(route('events.index'));
        $indexResponse2->assertStatus(200);
        $indexResponse2->assertSee('Pemilihan Dibuat Petugas');

        // Both super admins can view show, edit, and monitoring
        $showResponse = $this->actingAs($secondAdmin)->get(route('events.show', $event));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Pemilihan Dibuat Petugas');

        $editResponse = $this->actingAs($secondAdmin)->get(route('events.edit', $event));
        $editResponse->assertStatus(200);

        $monitoringResponse = $this->actingAs($secondAdmin)->get(route('events.monitoring', $event));
        $monitoringResponse->assertStatus(200);
    }

    public function test_super_admins_are_attached_when_petugas_creates_event_via_api(): void
    {
        $petugas = User::create([
            'nama' => 'Petugas API',
            'email' => 'petugasapi@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $petugasToken = $petugas->createToken('user_token')->plainTextToken;

        $payload = [
            'title' => 'Pemilihan API Petugas',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'description' => 'Event via api petugas',
            'start_time' => Carbon::now()->toIso8601String(),
            'end_time' => Carbon::now()->addDays(2)->toIso8601String(),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->postJson('/api/v1/petugas/events', $payload);
        $response->assertStatus(201);

        $createdEvent = Event::where('title', 'Pemilihan API Petugas')->firstOrFail();
        $this->assertTrue($createdEvent->officers->contains('id', $this->admin->id));
    }

    public function test_newly_created_super_admin_is_attached_to_existing_events(): void
    {
        $adminPayload = [
            'nama' => 'Admin Baru Ditambahkan',
            'email' => 'adminbaru@evoting.sch.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'SUPER_ADMIN',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $adminPayload);
        $response->assertRedirect(route('admin.users.index'));

        $newAdmin = User::where('email', 'adminbaru@evoting.sch.id')->firstOrFail();
        $this->assertTrue($this->event->officers->contains('id', $newAdmin->id));
    }

    public function test_petugas_can_only_see_and_access_own_created_event_and_not_other_petugas_event(): void
    {
        $petugas1 = User::create([
            'nama' => 'Panitia OSIS',
            'email' => 'panitia.osis@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $petugas2 = User::create([
            'nama' => 'Panitia MPK',
            'email' => 'panitia.mpk@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        // Petugas 1 creates an event
        $payload1 = [
            'title' => 'Pemilihan OSIS 2026',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'room_code' => 'OSIS99',
            'description' => 'Event OSIS',
            'start_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(2)->format('Y-m-d\TH:i'),
        ];
        $this->actingAs($petugas1)->post(route('events.store'), $payload1);
        $eventOsis = Event::where('room_code', 'OSIS99')->firstOrFail();

        // Petugas 2 creates an event
        $payload2 = [
            'title' => 'Pemilihan MPK 2026',
            'category' => 'MPK',
            'target_scope' => 'ALL',
            'room_code' => 'MPK099',
            'description' => 'Event MPK',
            'start_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'end_time' => Carbon::now()->addDays(2)->format('Y-m-d\TH:i'),
        ];
        $this->actingAs($petugas2)->post(route('events.store'), $payload2);
        $eventMpk = Event::where('room_code', 'MPK099')->firstOrFail();

        $this->flushSession();

        // Petugas 1 sees only OSIS 2026 in events.index
        $resPetugas1 = $this->actingAs($petugas1)->get(route('events.index'));
        $resPetugas1->assertStatus(200);
        $resPetugas1->assertSee('Pemilihan OSIS 2026');
        $resPetugas1->assertDontSee('Pemilihan MPK 2026');

        $this->flushSession();

        // Petugas 2 sees only MPK 2026 in events.index
        $resPetugas2 = $this->actingAs($petugas2)->get(route('events.index'));
        $resPetugas2->assertStatus(200);
        $resPetugas2->assertSee('Pemilihan MPK 2026');
        $resPetugas2->assertDontSee('Pemilihan OSIS 2026');

        // Super Admin sees both
        $resAdmin = $this->actingAs($this->admin)->get(route('events.index'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Pemilihan OSIS 2026');
        $resAdmin->assertSee('Pemilihan MPK 2026');

        // Petugas 1 cannot access Petugas 2's event (403)
        $this->actingAs($petugas1)->get(route('events.show', $eventMpk))->assertStatus(403);
        $this->actingAs($petugas1)->get(route('events.edit', $eventMpk))->assertStatus(403);
        $this->actingAs($petugas1)->get(route('events.monitoring', $eventMpk))->assertStatus(403);
        $this->actingAs($petugas1)->get(route('events.candidates.index', $eventMpk))->assertStatus(403);

        // Petugas 2 cannot access Petugas 1's event (403)
        $this->actingAs($petugas2)->get(route('events.show', $eventOsis))->assertStatus(403);
        $this->actingAs($petugas2)->get(route('events.edit', $eventOsis))->assertStatus(403);
        $this->actingAs($petugas2)->get(route('events.monitoring', $eventOsis))->assertStatus(403);
        $this->actingAs($petugas2)->get(route('events.candidates.index', $eventOsis))->assertStatus(403);

        // Super Admin can access both events
        $this->actingAs($this->admin)->get(route('events.show', $eventOsis))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('events.show', $eventMpk))->assertStatus(200);
    }

    public function test_petugas_dashboard_counts_and_lists_only_own_events(): void
    {
        $petugas1 = User::create([
            'nama' => 'Panitia A',
            'email' => 'panitia.a@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $petugas2 = User::create([
            'nama' => 'Panitia B',
            'email' => 'panitia.b@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        Event::create([
            'title' => 'Event Petugas A',
            'room_code' => 'EVTA01',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'status' => 'PUBLISHED',
            'created_by_user_id' => $petugas1->id,
        ]);

        Event::create([
            'title' => 'Event Petugas B',
            'room_code' => 'EVTB01',
            'category' => 'MPK',
            'target_scope' => 'ALL',
            'status' => 'PUBLISHED',
            'created_by_user_id' => $petugas2->id,
        ]);

        // Petugas A dashboard
        $resA = $this->actingAs($petugas1)->get(route('dashboard'));
        $resA->assertStatus(200);
        $resA->assertSee('Event Petugas A');
        $resA->assertDontSee('Event Petugas B');
        $resA->assertViewHas('totalEvents', 1);

        // Petugas B dashboard
        $resB = $this->actingAs($petugas2)->get(route('dashboard'));
        $resB->assertStatus(200);
        $resB->assertSee('Event Petugas B');
        $resB->assertDontSee('Event Petugas A');
        $resB->assertViewHas('totalEvents', 1);

        // Admin dashboard sees all 3 events (including $this->event from setUp)
        $resAdmin = $this->actingAs($this->admin)->get(route('dashboard'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Event Petugas A');
        $resAdmin->assertSee('Event Petugas B');
        $resAdmin->assertViewHas('totalEvents', 3);
    }
}
