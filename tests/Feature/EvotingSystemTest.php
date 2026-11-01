<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\User;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EvotingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Voter $voter;
    protected Event $event;
    protected Candidate $candidate1;
    protected Candidate $candidate2;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin
        $this->admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        // Setup Voter
        $this->voter = Voter::create([
            'nama' => 'Budi Siswa',
            'identifier' => '123456',
            'password' => Hash::make('pass123'),
            'type' => 'SISWA',
            'kelas' => 'XII IPA 1',
            'is_active' => true,
        ]);

        // Setup Active Event
        $this->event = Event::create([
            'title' => 'Pemilihan Ketua OSIS Test',
            'room_code' => 'OSIS99',
            'description' => 'Uji coba pemilihan OSIS',
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'status' => 'PUBLISHED',
            'category' => 'OSIS',
            'created_by_user_id' => $this->admin->id,
        ]);

        // Setup Candidates
        $this->candidate1 = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 1,
            'name' => 'Calon Satu & Wakil Satu',
            'vision' => 'Visi Calon 1',
            'mission' => 'Misi Calon 1',
        ]);

        $this->candidate2 = Candidate::create([
            'event_id' => $this->event->id,
            'candidate_number' => 2,
            'name' => 'Calon Dua & Wakil Dua',
            'vision' => 'Visi Calon 2',
            'mission' => 'Misi Calon 2',
        ]);
    }

    public function test_landing_page_redirects_directly_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));

        $loginResponse = $this->followingRedirects()->get('/');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Masuk Pemilih');
        $loginResponse->assertDontSee('Sesi Aktif');
        $loginResponse->assertDontSee('Gunakan #');
    }

    public function test_voter_authentication(): void
    {
        // Failed attempt with unregistered NISN
        $fail = $this->post(route('voter.login.submit'), [
            'identifier' => '999999',
            'room_code' => 'OSIS99',
        ]);
        $fail->assertSessionHasErrors('identifier');

        // Failed attempt with invalid room code
        $failRoom = $this->post(route('voter.login.submit'), [
            'identifier' => '123456',
            'room_code' => 'INVALID_ROOM',
        ]);
        $failRoom->assertSessionHasErrors('room_code');

        // Successful direct login to voting booth
        $success = $this->post(route('voter.login.submit'), [
            'identifier' => '123456',
            'room_code' => 'OSIS99',
        ]);
        $success->assertRedirect(route('voter.booth', $this->event->room_code));
        $this->assertAuthenticatedAs($this->voter, 'voter');
    }

    public function test_admin_authentication(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'web');
    }

    public function test_voter_can_cast_vote_and_segregation_guarantee(): void
    {
        // 1. Voter enters the room
        $enter = $this->actingAs($this->voter, 'voter')
            ->post(route('voter.room.join'), [
                'room_code' => 'OSIS99',
            ]);

        $enter->assertRedirect(route('voter.booth', $this->event->room_code));

        // 2. Voter casts vote for candidate 1
        $voteAction = $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event->room_code), [
                'candidate_id' => $this->candidate1->id,
            ]);

        $voteAction->assertRedirect(route('voter.success', $this->event->room_code));

        // 3. Verify Vote table has the vote count, but NO voter_id column
        $this->assertDatabaseHas('votes', [
            'event_id' => $this->event->id,
            'candidate_id' => $this->candidate1->id,
        ]);
        $this->assertEquals(1, Vote::where('event_id', $this->event->id)->count());

        // 4. Verify Participation is recorded as voted
        $this->assertDatabaseHas('event_participations', [
            'event_id' => $this->event->id,
            'voter_id' => $this->voter->id,
            'has_voted' => true,
        ]);
    }

    public function test_double_voting_prevention_strictly_blocks_second_vote(): void
    {
        // Cast first vote
        $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event->room_code), [
                'candidate_id' => $this->candidate1->id,
            ]);

        // Attempt second vote for candidate 2
        $secondAttempt = $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event->room_code), [
                'candidate_id' => $this->candidate2->id,
            ]);

        // Must redirect to already voted page and NOT increase vote count
        $secondAttempt->assertRedirect(route('voter.voted', $this->event->room_code));
        $this->assertEquals(1, Vote::where('event_id', $this->event->id)->count());
    }

    public function test_voter_relogin_after_voting_redirects_directly_to_already_voted(): void
    {
        // Mark voter as participated
        EventParticipation::create([
            'event_id' => $this->event->id,
            'voter_id' => $this->voter->id,
            'has_voted' => true,
            'voted_at' => now(),
        ]);

        // Voter attempts login again
        $response = $this->post(route('voter.login.submit'), [
            'identifier' => '123456',
            'room_code' => 'OSIS99',
        ]);

        $response->assertRedirect(route('voter.voted', $this->event->room_code));
    }

    public function test_logged_in_voter_bypasses_agenda_catalog_directly_to_booth(): void
    {
        $response = $this->actingAs($this->voter, 'voter')
            ->withSession(['voter_room_code' => 'OSIS99'])
            ->get(route('voter.index'));

        $response->assertRedirect(route('voter.booth', $this->event->room_code));
    }

    public function test_monitoring_json_and_berita_acara_pdf(): void
    {
        // First vote
        $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $this->event->room_code), [
                'candidate_id' => $this->candidate1->id,
            ]);

        // Monitoring JSON API accessible by admin
        $json = $this->actingAs($this->admin, 'web')
            ->getJson(route('events.live-stats', $this->event));

        $json->assertStatus(200);
        $json->assertJsonStructure([
            'status',
            'is_active',
            'total_dpt',
            'total_votes',
            'turnout_rate',
            'non_voters',
            'candidates',
            'timestamp',
        ]);
        $json->assertJsonPath('total_votes', 1);
        $json->assertJsonPath('candidates.0.votes', 1);
        $json->assertJsonPath('candidates.0.percentage', 100);
        $json->assertJsonPath('candidates.1.votes', 0);
        $json->assertJsonPath('candidates.1.percentage', 0);

        // Alias endpoint /events/{event}/live-stats
        $aliasJson = $this->actingAs($this->admin, 'web')
            ->getJson("/events/{$this->event->id}/live-stats");
        $aliasJson->assertStatus(200);
        $aliasJson->assertJsonPath('candidates.0.votes', 1);

        // Monitoring view contains votes in stats JSON
        $view = $this->actingAs($this->admin, 'web')
            ->get(route('events.monitoring', $this->event));
        $view->assertStatus(200);
        $view->assertSee('"votes":1');

        // Berita Acara PDF download
        $pdf = $this->actingAs($this->admin, 'web')
            ->get(route('events.report.pdf', $this->event));

        $pdf->assertStatus(200);
        $this->assertEquals('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_role_authorization_petugas_cannot_access_super_admin_routes(): void
    {
        $petugas = User::create([
            'nama' => 'Petugas Biasa',
            'email' => 'petugas.test@test.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);

        // Accessing super admin user management must be forbidden (403)
        $response = $this->actingAs($petugas, 'web')
            ->get(route('admin.users.index'));

        $response->assertStatus(403);

        // Accessing super admin audit logs must be forbidden (403)
        $logs = $this->actingAs($petugas, 'web')
            ->get(route('admin.logs.index'));

        $logs->assertStatus(403);
    }

    public function test_super_admin_can_export_dpt_csv(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.voters.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }

    public function test_class_scope_allows_matching_class_and_blocks_unauthorized_class(): void
    {
        // Setup class-specific election for XII IPA 1
        $classEvent = Event::create([
            'title' => 'Pemilihan Ketua Kelas XII IPA 1',
            'room_code' => 'IPA101',
            'status' => 'PUBLISHED',
            'category' => 'LAINNYA',
            'target_scope' => 'KELAS',
            'target_class' => 'XII IPA 1',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $classEvent->id,
            'candidate_number' => 1,
            'name' => 'Calon Ketua Kelas 1',
        ]);
        Candidate::create([
            'event_id' => $classEvent->id,
            'candidate_number' => 2,
            'name' => 'Calon Ketua Kelas 2',
        ]);

        // Student from other class (X-RPL-1)
        $otherStudent = Voter::create([
            'nama' => 'Siswa Kelas Lain',
            'identifier' => '654321',
            'password' => Hash::make('pass123'),
            'type' => 'SISWA',
            'kelas' => 'X-RPL-1',
            'is_active' => true,
        ]);

        // 1. Other student attempts login into XII IPA 1 booth -> Rejected by Scope Gating
        $rejectResponse = $this->post(route('voter.login.submit'), [
            'identifier' => '654321',
            'room_code' => 'IPA101',
        ]);

        $rejectResponse->assertSessionHasErrors('identifier');
        $this->assertGuest('voter');

        // 2. Eligible student ($this->voter is in XII IPA 1) attempts login -> Success
        $successResponse = $this->post(route('voter.login.submit'), [
            'identifier' => '123456',
            'room_code' => 'IPA101',
        ]);

        $successResponse->assertRedirect(route('voter.booth', 'IPA101'));
        $this->assertAuthenticatedAs($this->voter, 'voter');
    }

    public function test_teacher_scope_allows_teachers_and_blocks_students(): void
    {
        $teacherEvent = Event::create([
            'title' => 'Pemilihan Komite Guru',
            'room_code' => 'GURU99',
            'status' => 'PUBLISHED',
            'category' => 'KOMITE',
            'target_scope' => 'GURU',
            'created_by_user_id' => $this->admin->id,
        ]);

        Candidate::create([
            'event_id' => $teacherEvent->id,
            'candidate_number' => 1,
            'name' => 'Calon Guru 1',
        ]);
        Candidate::create([
            'event_id' => $teacherEvent->id,
            'candidate_number' => 2,
            'name' => 'Calon Guru 2',
        ]);

        $teacherVoter = Voter::create([
            'nama' => 'Pak Guru Budi',
            'identifier' => '19850101',
            'type' => 'GURU',
            'is_active' => true,
        ]);

        // Student attempts login -> Blocked
        $studentLogin = $this->post(route('voter.login.submit'), [
            'identifier' => $this->voter->identifier,
            'room_code' => 'GURU99',
        ]);
        $studentLogin->assertSessionHasErrors('identifier');

        // Teacher attempts login -> Allowed
        $teacherLogin = $this->post(route('voter.login.submit'), [
            'identifier' => '19850101',
            'room_code' => 'GURU99',
        ]);
        $teacherLogin->assertRedirect(route('voter.booth', 'GURU99'));
    }

    public function test_eligible_voters_count_is_scoped_accurately(): void
    {
        // 1 Teacher, 1 Student XII IPA 1 (from setUp), add 2 students from X-RPL
        Voter::create([
            'nama' => 'Guru A',
            'identifier' => 'NIP001',
            'password' => Hash::make('pass'),
            'type' => 'GURU',
            'is_active' => true,
        ]);
        Voter::create([
            'nama' => 'Siswa RPL 1',
            'identifier' => 'NISN101',
            'password' => Hash::make('pass'),
            'type' => 'SISWA',
            'kelas' => 'X-RPL',
            'is_active' => true,
        ]);
        Voter::create([
            'nama' => 'Siswa RPL 2',
            'identifier' => 'NISN102',
            'password' => Hash::make('pass'),
            'type' => 'SISWA',
            'kelas' => 'X-RPL',
            'is_active' => true,
        ]);

        // General event: ALL (1 + 1 + 2 = 4 voters)
        $allEvent = Event::create([
            'title' => 'Event Umum',
            'room_code' => 'ALL001',
            'target_scope' => 'ALL',
            'created_by_user_id' => $this->admin->id,
        ]);
        $this->assertEquals(4, $allEvent->eligibleVotersCount());

        // Student-only event: SISWA (1 + 2 = 3 voters)
        $siswaEvent = Event::create([
            'title' => 'Event Siswa',
            'room_code' => 'SIS001',
            'target_scope' => 'SISWA',
            'created_by_user_id' => $this->admin->id,
        ]);
        $this->assertEquals(3, $siswaEvent->eligibleVotersCount());

        // Class event: KELAS X-RPL (2 voters)
        $classEvent = Event::create([
            'title' => 'Event Kelas X-RPL',
            'room_code' => 'RPL001',
            'target_scope' => 'KELAS',
            'target_class' => 'X-RPL',
            'created_by_user_id' => $this->admin->id,
        ]);
        $this->assertEquals(2, $classEvent->eligibleVotersCount());

        // Teacher event: GURU (1 voter)
        $guruEvent = Event::create([
            'title' => 'Event Guru',
            'room_code' => 'GUR001',
            'target_scope' => 'GURU',
            'created_by_user_id' => $this->admin->id,
        ]);
        $this->assertEquals(1, $guruEvent->eligibleVotersCount());
    }

    public function test_voter_cannot_login_if_event_schedule_is_in_the_future(): void
    {
        $futureEvent = Event::create([
            'title' => 'Pemilihan Masa Depan',
            'room_code' => 'FUTR01',
            'status' => 'PUBLISHED',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDays(2),
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->post(route('voter.login.submit'), [
            'identifier' => $this->voter->identifier,
            'room_code' => 'FUTR01',
        ]);

        $response->assertSessionHasErrors('room_code');
        $this->assertGuest('voter');
    }

    public function test_voter_cannot_login_if_event_schedule_has_expired(): void
    {
        $pastEvent = Event::create([
            'title' => 'Pemilihan Waktu Lampau',
            'room_code' => 'PAST01',
            'status' => 'PUBLISHED',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subHour(),
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->post(route('voter.login.submit'), [
            'identifier' => $this->voter->identifier,
            'room_code' => 'PAST01',
        ]);

        $response->assertSessionHasErrors('room_code');
        $this->assertEquals('CLOSED', $pastEvent->fresh()->status);
        $this->assertGuest('voter');
    }

    public function test_voter_cannot_submit_vote_if_event_expires_while_in_booth(): void
    {
        // Setup event that ends in 1 second, then expired
        $expiringEvent = Event::create([
            'title' => 'Pemilihan Habis Waktu',
            'room_code' => 'EXPBOOTH',
            'status' => 'PUBLISHED',
            'category' => 'OSIS',
            'target_scope' => 'ALL',
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->subSecond(),
            'created_by_user_id' => $this->admin->id,
        ]);

        $candidate = Candidate::create([
            'event_id' => $expiringEvent->id,
            'candidate_number' => 1,
            'name' => 'Calon Expired',
        ]);

        $response = $this->actingAs($this->voter, 'voter')
            ->post(route('voter.vote.submit', $expiringEvent->room_code), [
                'candidate_id' => $candidate->id,
            ]);

        $response->assertRedirect(route('voter.login'));
        $response->assertSessionHas('error');
        $this->assertEquals(0, Vote::where('event_id', $expiringEvent->id)->count());
        $this->assertEquals('CLOSED', $expiringEvent->fresh()->status);
    }

    public function test_expired_draft_or_published_event_displays_ditutup_and_not_draft(): void
    {
        $expiredEvent = Event::create([
            'title' => 'Pemilihan Kadaluarsa',
            'room_code' => 'EXPD01',
            'category' => 'OSIS',
            'status' => 'DRAFT',
            'start_time' => Carbon::now()->subHours(5),
            'end_time' => Carbon::now()->subMinutes(10),
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('events.show', $expiredEvent));
        $response->assertOk();
        $response->assertSee('Ditutup');
        $response->assertDontSee('Draft / Persiapan');
        $this->assertEquals('CLOSED', $expiredEvent->fresh()->status);

        $indexResponse = $this->actingAs($this->admin)->get(route('events.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Ditutup');

        $dashResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Ditutup');
    }
}
