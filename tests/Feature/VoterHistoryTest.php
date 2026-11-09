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

class VoterHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Voter $voter;
    protected Event $event1;
    protected Event $event2;
    protected Candidate $candidateE1C1;
    protected Candidate $candidateE1C2;
    protected Candidate $candidateE2C1;
    protected Candidate $candidateE2C2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        $this->voter = Voter::create([
            'nama' => 'Ahmad Pemilih',
            'identifier' => '112233',
            'type' => 'SISWA',
            'kelas' => 'XII RPL 1',
            'is_active' => true,
        ]);

        $this->event1 = Event::create([
            'title' => 'Pemilihan Ketua OSIS 2025',
            'room_code' => 'OSIS01',
            'description' => 'Pemilihan OSIS tahunan',
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->addHours(2),
            'status' => 'PUBLISHED',
            'category' => 'OSIS',
            'created_by_user_id' => $this->admin->id,
        ]);

        $this->candidateE1C1 = Candidate::create([
            'event_id' => $this->event1->id,
            'candidate_number' => 1,
            'name' => 'Paslon Satu Rahasia',
            'vision' => 'Visi 1',
            'mission' => 'Misi 1',
        ]);

        $this->candidateE1C2 = Candidate::create([
            'event_id' => $this->event1->id,
            'candidate_number' => 2,
            'name' => 'Paslon Dua Rahasia',
            'vision' => 'Visi 2',
            'mission' => 'Misi 2',
        ]);

        $this->event2 = Event::create([
            'title' => 'Pemilihan Ketua MPK 2025',
            'room_code' => 'MPK01',
            'description' => 'Pemilihan MPK tahunan',
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(3),
            'status' => 'PUBLISHED',
            'category' => 'MPK',
            'created_by_user_id' => $this->admin->id,
        ]);

        $this->candidateE2C1 = Candidate::create([
            'event_id' => $this->event2->id,
            'candidate_number' => 1,
            'name' => 'Paslon Satu MPK',
            'vision' => 'Visi MPK 1',
            'mission' => 'Misi MPK 1',
        ]);

        $this->candidateE2C2 = Candidate::create([
            'event_id' => $this->event2->id,
            'candidate_number' => 2,
            'name' => 'Paslon Dua MPK',
            'vision' => 'Visi MPK 2',
            'mission' => 'Misi MPK 2',
        ]);
    }

    public function test_guest_cannot_access_voter_history_page(): void
    {
        $response = $this->get(route('voter.history'));

        $response->assertRedirect(route('voter.login'));
    }

    public function test_authenticated_voter_can_access_history_with_empty_state(): void
    {
        $response = $this->actingAs($this->voter, 'voter')
            ->get(route('voter.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat &amp; Log Pemilihan', false);
        $response->assertSee('Belum Ada Riwayat Pemilihan');
        $response->assertSee('Ahmad Pemilih');
        $response->assertSee('112233');
        $response->assertSee('Jaminan Kerahasiaan Suara (Asas LUBER)');
    }

    public function test_voting_creates_audit_log_without_exposing_candidate_choice(): void
    {
        // Cast vote in event1 for candidate 1
        $response = $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event1->room_code), [
                'candidate_id' => $this->candidateE1C1->id,
            ]);

        $response->assertRedirect(route('voter.success', $this->event1->room_code));

        // Check AuditLog table for voter action
        $auditLog = AuditLog::where('user_type', 'VOTER')
            ->where('user_id', $this->voter->id)
            ->where('action', 'SUARA_DISALURKAN')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('Pemilihan Ketua OSIS 2025', $auditLog->payload['event_title']);
        $this->assertEquals('OSIS01', $auditLog->payload['room_code']);

        // Strictly verify ballot secrecy (Asas LUBER): no candidate data in audit log payload
        $this->assertArrayNotHasKey('candidate_id', $auditLog->payload);
        $this->assertArrayNotHasKey('candidate_name', $auditLog->payload);
        $this->assertStringNotContainsString('Paslon Satu Rahasia', json_encode($auditLog->payload));
    }

    public function test_voter_history_displays_multi_event_participations_and_audit_logs(): void
    {
        // Vote in event 1
        $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event1->room_code), [
                'candidate_id' => $this->candidateE1C1->id,
            ]);

        // Vote in event 2
        $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event2->room_code), [
                'candidate_id' => $this->candidateE2C1->id,
            ]);

        // Access voter history page
        $historyResponse = $this->actingAs($this->voter, 'voter')
            ->get(route('voter.history'));

        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('Pemilihan Ketua OSIS 2025');
        $historyResponse->assertSee('Pemilihan Ketua MPK 2025');
        $historyResponse->assertSee('Suara Disalurkan');
        $historyResponse->assertSee('OSIS01');
        $historyResponse->assertSee('MPK01');
        $historyResponse->assertSee('Suara Sah Terverifikasi');
        $historyResponse->assertSee('Jaminan Kerahasiaan Suara (Asas LUBER)');
    }

    public function test_post_vote_and_already_voted_pages_include_history_cta_navigation(): void
    {
        // Vote in event 1
        $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event1->room_code), [
                'candidate_id' => $this->candidateE1C1->id,
            ]);

        // Success page has link to history
        $successPage = $this->actingAs($this->voter, 'voter')
            ->get(route('voter.success', $this->event1->room_code));
        $successPage->assertStatus(200);
        $successPage->assertSee(route('voter.history'));
        $successPage->assertSee('Lihat Riwayat &amp; Log', false);

        // Already voted page has link to history
        $alreadyVotedPage = $this->actingAs($this->voter, 'voter')
            ->get(route('voter.voted', $this->event1->room_code));
        $alreadyVotedPage->assertStatus(200);
        $alreadyVotedPage->assertSee(route('voter.history'));
        $alreadyVotedPage->assertSee('Lihat Riwayat &amp; Log', false);

        // When all active events are ended/completed, voter portal index page displays quick access card to history
        Event::query()->update(['status' => 'CLOSED', 'end_time' => now()->subMinutes(5)]);

        $portalPage = $this->actingAs($this->voter, 'voter')
            ->get(route('voter.index'));
        $portalPage->assertStatus(200);
        $portalPage->assertSee(route('voter.history'));
        $portalPage->assertSee('Log &amp; Riwayat Partisipasi Pemilihan', false);
    }
}
