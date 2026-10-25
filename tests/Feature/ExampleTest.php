<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example: Entry point redirects to voter login successfully.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
    }

    public function test_voter_login_renders_cleanly_without_duplication(): void
    {
        $response = $this->get('/voter/login');

        $response->assertStatus(200);
        $response->assertDontSee('Panitia / Admin');
        $content = $response->getContent();
        
        // Ensure single rendering without layout duplication
        $this->assertEquals(1, substr_count($content, 'Otentikasi Pemilih'));
        $this->assertEquals(1, substr_count($content, 'action="' . route('voter.login.submit') . '"'));
        $this->assertEquals(1, substr_count($content, 'name="identifier"'));
    }

    public function test_admin_login_renders_cleanly_without_duplication(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Ensure single rendering without layout duplication
        $this->assertEquals(1, substr_count($content, 'Portal Panitia & Admin'));
        $this->assertEquals(1, substr_count($content, 'action="' . route('admin.login.submit') . '"'));
        $this->assertEquals(1, substr_count($content, 'name="email"'));
    }
}
