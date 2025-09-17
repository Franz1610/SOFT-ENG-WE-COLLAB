<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    // Admin should get 200
    $this->actingAs($admin);
    $responseAdmin = $this->get('/dashboard');
    $responseAdmin->assertStatus(200);

    // Normal user should be redirected to homepage
    $this->actingAs($user);
    $responseUser = $this->get('/dashboard');
    $responseUser->assertRedirect('/');
    }
}
