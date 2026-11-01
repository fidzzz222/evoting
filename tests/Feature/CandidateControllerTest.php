<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $officer;
    protected User $otherUser;
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

        $this->officer = User::create([
            'nama' => 'Panitia Pemilihan',
            'email' => 'panitia@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $this->otherUser = User::create([
            'nama' => 'User Lain',
            'email' => 'lain@evoting.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
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

        // Attach officer to event
        $this->event->officers()->attach($this->officer->id);
    }

    public function test_admin_can_view_candidates_index(): void
    {
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Ahmad & Budi',
            'vision' => 'Visi 1',
            'mission' => 'Misi 1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('events.candidates.index', $this->event));

        $response->assertStatus(200);
        $response->assertSee('Ahmad');
        $response->assertSee('Budi');
        $response->assertSee('Kandidat 1');
    }

    public function test_officer_can_view_candidates_index(): void
    {
        $response = $this->actingAs($this->officer)->get(route('events.candidates.index', $this->event));

        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_view_candidates(): void
    {
        $response = $this->actingAs($this->otherUser)->get(route('events.candidates.index', $this->event));

        $response->assertStatus(403);
    }

    public function test_can_view_create_candidate_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('events.candidates.create', $this->event));

        $response->assertStatus(200);
        $response->assertSee('Tambah Pasangan Calon Baru');
    }

    public function test_can_store_candidate_pair_using_form_fields(): void
    {
        $payload = [
            'candidate_number' => 1,
            'chairman_name' => 'Muhammad Raihan',
            'vice_chairman_name' => 'Siti Aisyah',
            'vision' => 'Mewujudkan OSIS yang unggul dan inklusif',
            'mission' => "1. Meningkatkan kedisiplinan\n2. Program digitalisasi",
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        // Paslon pertama akan mengarahkan kembali ke create paslon #2 karena wajib minimal 2 paslon
        $response->assertRedirect(route('events.candidates.create', $this->event));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidates', [
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Muhammad Raihan & Siti Aisyah',
            'vision' => 'Mewujudkan OSIS yang unggul dan inklusif',
        ]);
    }

    public function test_can_store_second_candidate_and_redirects_to_candidate_index(): void
    {
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $payload = [
            'candidate_number' => 2,
            'chairman_name' => 'Calon Kedua',
            'vice_chairman_name' => 'Wakil Kedua',
            'vision' => 'Visi 2',
            'mission' => 'Misi 2',
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        // Setelah paslon 2 disimpan, syarat minimal 2 paslon terpenuhi dan diarahkan ke daftar paslon
        $response->assertRedirect(route('events.candidates.index', $this->event));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidates', [
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Calon Kedua & Wakil Kedua',
        ]);
    }

    public function test_can_store_single_candidate_without_vice_chairman(): void
    {
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $payload = [
            'candidate_number' => 2,
            'chairman_name' => 'Muhammad Raihan',
            'vice_chairman_name' => '',
            'vision' => 'Visi tunggal',
            'mission' => 'Misi tunggal',
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        $response->assertRedirect(route('events.candidates.index', $this->event));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidates', [
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Muhammad Raihan',
        ]);
    }

    public function test_store_validation_fails_when_chairman_name_is_missing(): void
    {
        $payload = [
            'candidate_number' => 1,
            'chairman_name' => '',
            'vice_chairman_name' => 'Siti Aisyah',
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        $response->assertSessionHasErrors(['chairman_name']);
        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_store_validation_fails_on_duplicate_candidate_number(): void
    {
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $payload = [
            'candidate_number' => 1,
            'chairman_name' => 'Paslon Baru',
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        $response->assertSessionHasErrors(['candidate_number']);
    }

    public function test_can_store_candidate_with_photo_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('paslon.jpg', 600, 800);

        $payload = [
            'candidate_number' => 1,
            'chairman_name' => 'Calon Berfoto',
            'photo' => $file,
        ];

        $response = $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        $response->assertRedirect(route('events.candidates.create', $this->event));

        $candidate = Candidate::first();
        $this->assertNotNull($candidate->photo_url);
        $this->assertStringStartsWith('storage/candidates/', $candidate->photo_url);
    }

    public function test_can_view_edit_candidate_page(): void
    {
        $candidate = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Ahmad & Budi',
            'vision' => 'Visi awal',
            'mission' => 'Misi awal',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('events.candidates.edit', [$this->event, $candidate])
        );

        $response->assertStatus(200);
        $response->assertSee('Edit Pasangan Calon #1');
        $response->assertSee('Ahmad');
        $response->assertSee('Budi');
    }

    public function test_can_update_candidate_pair(): void
    {
        $candidate = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Ahmad & Budi',
            'vision' => 'Visi lama',
            'mission' => 'Misi lama',
        ]);

        $payload = [
            'candidate_number' => 2,
            'chairman_name' => 'Ahmad Fauzi',
            'vice_chairman_name' => 'Budi Santoso',
            'vision' => 'Visi baru',
            'mission' => 'Misi baru',
        ];

        $response = $this->actingAs($this->admin)->put(
            route('events.candidates.update', [$this->event, $candidate]),
            $payload
        );

        $response->assertRedirect(route('events.candidates.index', $this->event));
        $response->assertSessionHas('success');

        $candidate->refresh();
        $this->assertEquals(2, $candidate->candidate_number);
        $this->assertEquals('Ahmad Fauzi & Budi Santoso', $candidate->name);
        $this->assertEquals('Visi baru', $candidate->vision);
    }

    public function test_can_delete_candidate_without_votes(): void
    {
        $candidate = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon Hapus',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('events.candidates.index', $this->event))
            ->delete(route('events.candidates.destroy', [$this->event, $candidate]));

        $response->assertRedirect(route('events.candidates.index', $this->event));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
    }

    public function test_storing_second_candidate_automatically_activates_event_in_scheduled_window(): void
    {
        // Event created in setUp() is in scheduled window (start_time: subHour, end_time: addHour) and status DRAFT
        Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $this->assertEquals('DRAFT', $this->event->fresh()->status);

        $payload = [
            'candidate_number' => 2,
            'chairman_name' => 'Kandidat 2',
        ];

        $this->actingAs($this->admin)->post(
            route('events.candidates.store', $this->event),
            $payload
        );

        // Setelah paslon ke-2 terdaftar dan waktu jadwal sedang berjalan, event otomatis PUBLISHED
        $this->assertEquals('PUBLISHED', $this->event->fresh()->status);
    }

    public function test_deleting_candidate_reverts_published_event_to_draft_if_under_two_candidates(): void
    {
        // Set event ke PUBLISHED dengan 2 kandidat
        $candidate1 = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
        ]);

        $candidate2 = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Paslon 2',
        ]);

        $this->event->update(['status' => 'PUBLISHED']);
        $this->assertEquals('PUBLISHED', $this->event->fresh()->status);

        // Hapus salah satu kandidat sehingga hanya tersisa 1 kandidat (< 2)
        $response = $this->actingAs($this->admin)
            ->from(route('events.candidates.index', $this->event))
            ->delete(route('events.candidates.destroy', [$this->event, $candidate2]));

        $response->assertRedirect(route('events.candidates.index', $this->event));
        $response->assertSessionHas('success');

        // Status event harus otomatis kembali ke DRAFT karena paslon < 2
        $this->assertEquals('DRAFT', $this->event->fresh()->status);
    }
}
