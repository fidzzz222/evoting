<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MinorBugsResolutionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(string $email = 'admin@sekolah.sch.id'): User
    {
        return User::create([
            'nama' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
        ]);
    }

    private function createPetugas(string $email = 'petugas@sekolah.sch.id'): User
    {
        return User::create([
            'nama' => 'Petugas',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'PETUGAS',
        ]);
    }

    public function test_cannot_delete_last_super_admin()
    {
        $superAdmin = $this->createAdmin('admin1@sekolah.sch.id');
        $otherAdmin = $this->createAdmin('admin2@sekolah.sch.id');

        $this->actingAs($superAdmin);

        // When 2 super admins exist, otherAdmin can be deleted
        $response = $this->delete(route('admin.users.destroy', $otherAdmin));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);

        // Now only 1 super admin remains, attempt to delete $superAdmin
        $response = $this->delete(route('admin.users.destroy', $superAdmin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_cannot_demote_last_super_admin_to_petugas()
    {
        $superAdmin = $this->createAdmin();

        $this->actingAs($superAdmin);

        $response = $this->put(route('admin.users.update', $superAdmin), [
            'nama' => 'Super Admin Updated',
            'email' => $superAdmin->email,
            'role' => 'PETUGAS',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('SUPER_ADMIN', $superAdmin->fresh()->role);
    }

    public function test_cannot_delete_officer_who_owns_events_via_api()
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $officer = $this->createPetugas();

        Event::create([
            'created_by_user_id' => $officer->id,
            'title' => 'Event Milik Petugas',
            'room_code' => 'EVT001',
            'status' => 'DRAFT',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/admin/petugas/{$officer->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Petugas tidak dapat dihapus karena masih memiliki event pemilihan yang dibuat.',
            ]);

        $this->assertDatabaseHas('users', ['id' => $officer->id]);
        $this->assertDatabaseHas('events', ['title' => 'Event Milik Petugas']);
    }

    public function test_can_delete_officer_without_events_via_api()
    {
        $admin = $this->createAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $officer = $this->createPetugas();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/admin/petugas/{$officer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Petugas berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $officer->id]);
    }

    public function test_candidate_photo_cleanup_on_web_update_with_slash_and_without_slash()
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $event = Event::create([
            'created_by_user_id' => $admin->id,
            'title' => 'Event Test Web Update',
            'room_code' => 'EVT002',
            'status' => 'DRAFT',
        ]);

        // Put a fake image in storage
        $oldFile = UploadedFile::fake()->image('old_photo.jpg');
        $path = $oldFile->store('candidates', 'public');
        Storage::disk('public')->assertExists($path);

        // candidate created via API format: '/storage/candidates/...'
        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Calon Lama',
            'photo_url' => '/storage/' . $path,
        ]);

        $this->actingAs($admin);

        $newFile = UploadedFile::fake()->image('new_photo.png');

        $response = $this->put(route('events.candidates.update', [$event, $candidate]), [
            'candidate_number' => 1,
            'chairman_name' => 'Calon Ketua Baru',
            'photo' => $newFile,
        ]);

        $response->assertSessionHas('success');

        // Old file must be cleaned up from disk
        Storage::disk('public')->assertMissing($path);
    }

    public function test_candidate_photo_cleanup_on_web_destroy()
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $event = Event::create([
            'created_by_user_id' => $admin->id,
            'title' => 'Event Test Web Destroy',
            'room_code' => 'EVT003',
            'status' => 'DRAFT',
        ]);

        $fakePhoto = UploadedFile::fake()->image('photo_to_delete.jpg');
        $path = $fakePhoto->store('candidates', 'public');
        Storage::disk('public')->assertExists($path);

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => 1,
            'name' => 'Calon Hapus',
            'photo_url' => 'storage/' . $path,
        ]);

        $this->actingAs($admin);

        $response = $this->delete(route('events.candidates.destroy', [$event, $candidate]));

        $response->assertSessionHas('success');
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('candidates', ['id' => $candidate->id]);
    }
}
