<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voter;
use App\Services\VoterImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoterImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_csv_import_creates_and_updates_voters_with_bom(): void
    {
        $admin = User::create([
            'nama' => 'Super Administrator',
            'email' => 'admin@evoting.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        // CSV content with UTF-8 BOM
        $csvContent = "\xEF\xBB\xBF" . "nama,nisn,type,kelas\n" .
            "Budi Santoso,12345,SISWA,XII-IPA-1\n" .
            "Siti Rahma,12346,GURU,Guru Matematika\n";

        $file = UploadedFile::fake()->createWithContent('dpt.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.voters.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.voters.index'));
        $this->assertDatabaseHas('voters', [
            'identifier' => '12345',
            'nama' => 'Budi Santoso',
            'type' => 'SISWA',
            'kelas' => 'XII-IPA-1',
        ]);
        $this->assertDatabaseHas('voters', [
            'identifier' => '12346',
            'nama' => 'Siti Rahma',
            'type' => 'GURU',
        ]);

        // Second import updates existing voter
        $csvUpdate = "nama,nisn,type,kelas\n" .
            "Budi Santoso Updated,12345,SISWA,XII-IPA-2\n";
        $updateFile = UploadedFile::fake()->createWithContent('dpt_update.csv', $csvUpdate);

        $this->actingAs($admin)->post(route('admin.voters.import'), [
            'csv_file' => $updateFile,
        ]);

        $this->assertDatabaseHas('voters', [
            'identifier' => '12345',
            'nama' => 'Budi Santoso Updated',
            'kelas' => 'XII-IPA-2',
        ]);
    }

    public function test_api_voter_import_handles_csv_and_array(): void
    {
        $admin = User::create([
            'nama' => 'Super Administrator 2',
            'email' => 'admin2@evoting.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'SUPER_ADMIN',
        ]);

        // API CSV import
        $csvContent = "identifier,nama,type\n" .
            "API001,Ahmad Siswa,SISWA\n" .
            "API002,Dewi Guru,GURU\n";

        $file = UploadedFile::fake()->createWithContent('voters.csv', $csvContent);

        Sanctum::actingAs($admin, ['*'], 'users');

        $response = $this->postJson('/api/v1/admin/voters/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('voters', ['identifier' => 'API001', 'nama' => 'Ahmad Siswa']);

        // Duplicate returns error in response
        $response2 = $this->postJson('/api/v1/admin/voters/import', [
            'voters' => [
                ['identifier' => 'API001', 'nama' => 'Duplicate', 'type' => 'SISWA'],
                ['identifier' => 'API003', 'nama' => 'New Voter', 'type' => 'SISWA'],
            ],
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('voters', ['identifier' => 'API003']);
        $this->assertCount(1, $response2->json('errors'));
    }
}
