<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MediumBugsResolutionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);
    }

    private function createPetugas(): User
    {
        return User::create([
            'nama' => 'Petugas Satu',
            'email' => 'petugas1@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);
    }

    public function test_voter_management_can_deactivate_voter(): void
    {
        $admin = $this->createAdmin();

        $voter = Voter::create([
            'identifier' => 'NISN_ACTIVE',
            'nama' => 'Siswa Aktif',
            'type' => 'SISWA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.voters.update', $voter), [
            'nisn' => $voter->identifier,
            'nama' => $voter->nama,
            'voter_type' => $voter->type,
            'kelas' => 'XII-RPL',
            // is_active checkbox unchecked (omitted from request)
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse($voter->fresh()->is_active);
    }

    public function test_api_candidate_scoped_number_unique_validation(): void
    {
        $petugas = $this->createPetugas();
        $token = $petugas->createToken('test')->plainTextToken;

        $eventA = Event::create([
            'title' => 'Event A',
            'room_code' => 'EVTA01',
            'status' => 'DRAFT',
            'created_by_user_id' => $petugas->id,
        ]);

        $eventB = Event::create([
            'title' => 'Event B',
            'room_code' => 'EVTB01',
            'status' => 'DRAFT',
            'created_by_user_id' => $petugas->id,
        ]);

        Candidate::create([
            'event_id' => $eventA->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1 A',
        ]);

        // Duplicate candidate_number in event A should return 422
        $responseFail = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/petugas/events/{$eventA->id}/candidates", [
                'candidate_number' => 1,
                'name' => 'Paslon 1 Duplicate A',
            ]);

        $responseFail->assertStatus(422);
        $responseFail->assertJsonValidationErrors(['candidate_number']);

        // Same candidate_number in event B should succeed
        $responseOk = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/petugas/events/{$eventB->id}/candidates", [
                'candidate_number' => 1,
                'name' => 'Paslon 1 in B',
            ]);

        $responseOk->assertStatus(201);
    }

    public function test_api_candidate_deletion_blocked_if_votes_exist(): void
    {
        $petugas = $this->createPetugas();
        $token = $petugas->createToken('test')->plainTextToken;

        $event = Event::create([
            'title' => 'Event With Votes',
            'room_code' => 'EVTV01',
            'status' => 'PUBLISHED',
            'created_by_user_id' => $petugas->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
        ]);

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Paslon Terpilih',
        ]);

        Vote::create([
            'event_id' => $event->id,
            'candidate_id' => $candidate->id,
            'voted_at' => Carbon::now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/petugas/candidates/{$candidate->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Kandidat tidak dapat dihapus karena pemilihan telah menerima suara sah.',
        ]);

        $this->assertDatabaseHas('candidates', ['id' => $candidate->id]);
    }

    public function test_api_monitoring_calculates_turnout_based_on_eligible_voters(): void
    {
        $petugas = $this->createPetugas();
        $token = $petugas->createToken('test')->plainTextToken;

        $event = Event::create([
            'title' => 'Pemilihan Dewan Guru',
            'room_code' => 'EVTG01',
            'status' => 'PUBLISHED',
            'target_scope' => 'GURU',
            'created_by_user_id' => $petugas->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
        ]);

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Calon Guru 1',
        ]);

        // 5 Guru, 15 Siswa
        for ($i = 1; $i <= 5; $i++) {
            Voter::create([
                'identifier' => "GURU{$i}",
                'nama' => "Guru {$i}",
                'type' => 'GURU',
                'is_active' => true,
            ]);
        }
        for ($i = 1; $i <= 15; $i++) {
            Voter::create([
                'identifier' => "SISWA{$i}",
                'nama' => "Siswa {$i}",
                'type' => 'SISWA',
                'is_active' => true,
            ]);
        }

        // 1 Guru votes
        Vote::create([
            'event_id' => $event->id,
            'candidate_id' => $candidate->id,
            'voted_at' => Carbon::now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/petugas/events/{$event->id}/monitor");

        $response->assertStatus(200);
        // Turnout should be 1/5 = 20%, not 1/20 = 5%
        $response->assertJsonPath('participation.total_voters', 5);
        $response->assertJsonPath('participation.total_voted', 1);
        $response->assertJsonPath('participation.turnout_percentage', 20);
    }

    public function test_api_event_store_and_update_supports_target_scope_and_target_class(): void
    {
        $petugas = $this->createPetugas();
        $token = $petugas->createToken('test')->plainTextToken;

        // Store with target_scope KELAS
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/petugas/events', [
                'title' => 'Pemilihan Ketua Kelas XII-RPL-1',
                'target_scope' => 'KELAS',
                'target_class' => 'XII-RPL-1',
            ]);

        $createResponse->assertStatus(201);
        $eventId = $createResponse->json('data.id');

        $this->assertDatabaseHas('events', [
            'id' => $eventId,
            'target_scope' => 'KELAS',
            'target_class' => 'XII-RPL-1',
        ]);

        // Update to target_scope GURU
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/petugas/events/{$eventId}", [
                'target_scope' => 'GURU',
            ]);

        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('events', [
            'id' => $eventId,
            'target_scope' => 'GURU',
            'target_class' => null,
        ]);
    }

    public function test_admin_cannot_delete_user_with_associated_events(): void
    {
        $admin = $this->createAdmin();
        $petugas = $this->createPetugas();

        Event::create([
            'title' => 'Event by Petugas',
            'room_code' => 'EVTP01',
            'status' => 'DRAFT',
            'created_by_user_id' => $petugas->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $petugas));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $petugas->id]);
    }

    public function test_voter_logout_clears_session_room_code(): void
    {
        $voter = Voter::create([
            'identifier' => 'NISN_LOGOUT',
            'nama' => 'Voter Logout',
            'type' => 'SISWA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($voter, 'voter')
            ->withSession(['voter_room_code' => 'ROOM123'])
            ->post(route('voter.logout'));

        $response->assertRedirect(route('voter.login'));
        $this->assertNull(session('voter_room_code'));
    }
}
