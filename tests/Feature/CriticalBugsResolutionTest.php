<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\User;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CriticalBugsResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_bug1_voter_dashboard_loads_when_no_active_event_without_infinite_redirect(): void
    {
        $voter = Voter::create([
            'identifier' => 'NISN1001',
            'nama' => 'Budi Santoso',
            'type' => 'SISWA',
            'is_active' => true,
        ]);

        // When authenticated voter visits dashboard without ongoing events, it renders 200 OK
        $response = $this->actingAs($voter, 'voter')->get(route('voter.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('voter.index');
        $response->assertSee('Budi Santoso');
    }

    public function test_bug2_voter_can_cast_vote_when_participation_record_has_voted_false(): void
    {
        $admin = User::create([
            'nama' => 'Panitia',
            'email' => 'panitia_test@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $voter = Voter::create([
            'identifier' => 'NISN1002',
            'nama' => 'Siti Nurhaliza',
            'type' => 'SISWA',
            'is_active' => true,
        ]);

        $event = Event::create([
            'title' => 'Pemilihan Ketua OSIS',
            'room_code' => 'OSIS99',
            'status' => 'PUBLISHED',
            'target_scope' => 'ALL',
            'created_by_user_id' => $admin->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
        ]);

        $c1 = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Paslon 1',
            'vision' => 'Visi 1',
            'mission' => 'Misi 1',
        ]);
        $c2 = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 2,
            'name' => 'Paslon 2',
            'vision' => 'Visi 2',
            'mission' => 'Misi 2',
        ]);

        // Participation exists with has_voted = false (e.g. joined via API)
        EventParticipation::create([
            'event_id' => $event->id,
            'voter_id' => $voter->id,
            'has_voted' => false,
        ]);

        // Voter can still access booth
        $boothResponse = $this->actingAs($voter, 'voter')->get(route('voter.booth', $event->room_code));
        $boothResponse->assertStatus(200);

        // Voter submits vote successfully without duplicate key error
        $voteResponse = $this->actingAs($voter, 'voter')->post(route('voter.vote', $event->room_code), [
            'candidate_id' => $c1->id,
        ]);

        $voteResponse->assertRedirect(route('voter.success', $event->room_code));
        $this->assertDatabaseHas('event_participations', [
            'event_id' => $event->id,
            'voter_id' => $voter->id,
            'has_voted' => true,
        ]);
        $this->assertDatabaseHas('votes', [
            'event_id' => $event->id,
            'candidate_id' => $c1->id,
        ]);
    }

    public function test_bug3_api_voter_login_requires_password_when_voter_has_password(): void
    {
        Voter::create([
            'identifier' => 'NISN1003',
            'nama' => 'Ahmad Dani',
            'type' => 'SISWA',
            'password' => Hash::make('secret_pass'),
            'is_active' => true,
        ]);

        // Attempt login without password -> should be rejected 401
        $noPassResponse = $this->postJson('/api/v1/auth/voter/login', [
            'identifier' => 'NISN1003',
        ]);
        $noPassResponse->assertStatus(401);

        // Attempt login with wrong password -> 401
        $wrongPassResponse = $this->postJson('/api/v1/auth/voter/login', [
            'identifier' => 'NISN1003',
            'password' => 'wrong_pass',
        ]);
        $wrongPassResponse->assertStatus(401);

        // Correct password -> 200
        $correctResponse = $this->postJson('/api/v1/auth/voter/login', [
            'identifier' => 'NISN1003',
            'password' => 'secret_pass',
        ]);
        $correctResponse->assertStatus(200);
    }

    public function test_bug4_api_voter_scope_eligibility_enforced(): void
    {
        $admin = User::create([
            'nama' => 'Panitia 2',
            'email' => 'panitia2_test@sekolah.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        $guruVoter = Voter::create([
            'identifier' => 'NIP1004',
            'nama' => 'Pak Guru Joko',
            'type' => 'GURU',
            'is_active' => true,
        ]);

        $eventSiswaOnly = Event::create([
            'title' => 'Pemilihan Ketua MPK Khusus Siswa',
            'room_code' => 'MPK001',
            'status' => 'PUBLISHED',
            'target_scope' => 'SISWA',
            'created_by_user_id' => $admin->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
        ]);

        $c1 = Candidate::create([
            'event_id' => $eventSiswaOnly->id,
            'candidate_number' => 1,
            'name' => 'Kandidat 1',
            'vision' => 'Visi',
            'mission' => 'Misi',
        ]);
        $c2 = Candidate::create([
            'event_id' => $eventSiswaOnly->id,
            'candidate_number' => 2,
            'name' => 'Kandidat 2',
            'vision' => 'Visi',
            'mission' => 'Misi',
        ]);

        $token = $guruVoter->createToken('test_token')->plainTextToken;

        // Guru trying to join SISWA-only event via API -> 403 Forbidden
        $joinResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/voter/events/join', [
                'room_code' => 'MPK001',
            ]);
        $joinResponse->assertStatus(403);

        // Guru trying to vote in SISWA-only event via API -> 403 Forbidden
        $voteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/voter/events/{$eventSiswaOnly->id}/vote", [
                'candidate_id' => $c1->id,
            ]);
        $voteResponse->assertStatus(403);
    }
}
