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

        $response->assertRedirect();
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
        $response = $this->actingAs($this->admin)->get(route('events.monitoring', $this->event));

        $response->assertStatus(200);
        $response->assertSee('Live Quick Count');
    }

    public function test_admin_can_delete_event(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('events.destroy', $this->event));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $this->event->id]);
    }
}
