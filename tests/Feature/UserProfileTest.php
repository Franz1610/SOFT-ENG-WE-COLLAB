<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_from_profile(): void
    {
        $response = $this->get('/profile');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    /** @test */
    public function authenticated_user_can_update_name_and_contact(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'contact' => null,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'contact' => '09171234567',
        ]);

        $response->assertRedirect(route('user.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'contact' => '09171234567',
        ]);
    }
}
