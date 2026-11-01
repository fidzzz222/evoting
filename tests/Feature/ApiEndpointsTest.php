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

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $petugas;
    protected Voter $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'nama' => 'Super Administrator',
            'email' => 'admin@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $this->petugas = User::create([
            'nama' => 'Petugas Panitia',
            'email' => 'petugas@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $this->voter = Voter::create([
            'identifier' => '1234567890',
            'nama' => 'Siswa Pemilih 1',
            'type' => 'SISWA',
            'password' => Hash::make('1234'),
            'is_active' => true,
        ]);
    }

    public function test_auth_login_for_user_and_voter()
    {
        // 1. User login (Petugas)
        $userResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'petugas@sekolah.sch.id',
            'password' => 'password123',
        ]);

        $userResponse->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'nama', 'email', 'role'],
            ]);

        $userToken = $userResponse->json('token');

        // Check auth/me with user token
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/v1/auth/me');
        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'petugas@sekolah.sch.id');

        // 2. Voter login
        $voterResponse = $this->postJson('/api/v1/auth/voter/login', [
            'identifier' => '1234567890',
            'password' => '1234',
        ]);

        $voterResponse->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'voter' => ['id', 'identifier', 'nama', 'type', 'is_active'],
            ]);

        $voterToken = $voterResponse->json('token');

        // Check voter auth/me
        $voterMeResponse = $this->withHeader('Authorization', 'Bearer ' . $voterToken)
            ->getJson('/api/v1/voter/auth/me');
        $voterMeResponse->assertStatus(200)
            ->assertJsonPath('user.identifier', '1234567890');
    }

    public function test_check_role_enforces_super_admin_restriction()
    {
        $petugasToken = $this->petugas->createToken('user_token')->plainTextToken;

        // Petugas attempting to access super admin admin management route should get 403
        $forbiddenResponse = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->getJson('/api/v1/admin/admins');

        $forbiddenResponse->assertStatus(403)
            ->assertJson(['message' => 'Akses ditolak: Peran tidak memiliki izin']);

        // Super Admin access succeeds
        auth()->forgetGuards();
        $adminToken = $this->superAdmin->createToken('user_token')->plainTextToken;
        $allowedResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/admins');

        $allowedResponse->assertStatus(200);
    }

    public function test_petugas_event_creation_and_management_flow()
    {
        $petugasToken = $this->petugas->createToken('user_token')->plainTextToken;

        // 1. Create event
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->postJson('/api/v1/petugas/events', [
                'title' => 'Pemilihan Ketua OSIS 2026/2027',
                'category' => 'KETUA_OSIS',
                'description' => 'Pemilihan ketua dan wakil ketua osis',
                'room_code' => 'OSIS26',
                'start_time' => Carbon::now()->subHour()->toDateTimeString(),
                'end_time' => Carbon::now()->addHours(5)->toDateTimeString(),
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.room_code', 'OSIS26');

        $eventId = $createResponse->json('data.id');

        // 2. Add candidates
        $candidate1 = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->postJson("/api/v1/petugas/events/{$eventId}/candidates", [
                'candidate_number' => 1,
                'name' => 'Paslon 01 - Budi & Citra',
                'description' => 'Maju bersama kita bisa',
                'vision' => 'Visi sekolah berprestasi',
                'mission' => 'Misi program unggulan',
            ]);
        $candidate1->assertStatus(201);
        $candidate1Id = $candidate1->json('data.id');

        $candidate2 = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->postJson("/api/v1/petugas/events/{$eventId}/candidates", [
                'candidate_number' => 2,
                'name' => 'Paslon 02 - Dika & Eka',
                'description' => 'Generasi kreatif dan inovatif',
                'vision' => 'Visi kreatif',
                'mission' => 'Misi inovasi',
            ]);
        $candidate2->assertStatus(201);

        // 3. Publish event
        $publishResponse = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->patchJson("/api/v1/petugas/events/{$eventId}/status", [
                'status' => 'PUBLISHED',
            ]);
        $publishResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'PUBLISHED');

        // 4. Invite info & collaboration
        $inviteInfo = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->getJson("/api/v1/petugas/events/{$eventId}/invite-info");
        $inviteInfo->assertStatus(200)
            ->assertJsonStructure(['invite_token', 'event_id', 'title', 'officers']);

        $token = $inviteInfo->json('invite_token');

        // Another petugas joins via invite token
        auth()->forgetGuards();
        $petugas2 = User::create([
            'nama' => 'Petugas Kolaborator',
            'email' => 'kolaborator@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);
        $petugas2Token = $petugas2->createToken('user_token')->plainTextToken;

        $joinInviteResponse = $this->withHeader('Authorization', 'Bearer ' . $petugas2Token)
            ->postJson("/api/v1/petugas/events/join/{$token}");
        $joinInviteResponse->assertStatus(200)
            ->assertJsonPath('already_joined', false);

        return [$eventId, $candidate1Id];
    }

    public function test_voter_join_and_voting_flow()
    {
        // Setup event
        $event = Event::create([
            'created_by_user_id' => $this->petugas->id,
            'title' => 'Pemilihan Ketua MPK 2026',
            'category' => 'KETUA_MPK',
            'room_code' => 'MPK026',
            'status' => 'PUBLISHED',
            'start_time' => Carbon::now()->subMinutes(30),
            'end_time' => Carbon::now()->addHours(2),
        ]);

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Kandidat Tunggal MPK',
            'vision' => 'Visi MPK',
            'mission' => 'Misi MPK',
        ]);

        Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 2,
            'name' => 'Kandidat Kedua MPK',
            'vision' => 'Visi MPK 2',
            'mission' => 'Misi MPK 2',
        ]);

        $voterToken = $this->voter->createToken('voter_token')->plainTextToken;

        // 1. Voter joins booth by room code
        $joinResponse = $this->withHeader('Authorization', 'Bearer ' . $voterToken)
            ->postJson('/api/v1/voter/events/join', [
                'room_code' => 'MPK026',
            ]);

        $joinResponse->assertStatus(200)
            ->assertJsonPath('has_voted', false)
            ->assertJsonPath('event.title', 'Pemilihan Ketua MPK 2026');

        // 2. Voter submits vote
        $voteResponse = $this->withHeader('Authorization', 'Bearer ' . $voterToken)
            ->postJson("/api/v1/voter/events/{$event->id}/vote", [
                'candidate_id' => $candidate->id,
            ]);

        $voteResponse->assertStatus(201)
            ->assertJson(['message' => 'Pencoblosan suara berhasil']);

        // 3. Double voting prevented
        $doubleVoteResponse = $this->withHeader('Authorization', 'Bearer ' . $voterToken)
            ->postJson("/api/v1/voter/events/{$event->id}/vote", [
                'candidate_id' => $candidate->id,
            ]);

        $doubleVoteResponse->assertStatus(422)
            ->assertJson(['message' => 'Anda sudah pernah memilih pada event ini!']);

        // 4. View results
        $resultsResponse = $this->withHeader('Authorization', 'Bearer ' . $voterToken)
            ->getJson("/api/v1/voter/events/{$event->id}/results");

        $resultsResponse->assertStatus(200)
            ->assertJsonPath('total_votes', 1);

        // 5. Petugas monitoring
        $petugasToken = $this->petugas->createToken('user_token')->plainTextToken;
        $monitorResponse = $this->withHeader('Authorization', 'Bearer ' . $petugasToken)
            ->getJson("/api/v1/petugas/events/{$event->id}/monitor");

        $monitorResponse->assertStatus(200)
            ->assertJsonPath('participation.total_voted', 1);
    }
}
