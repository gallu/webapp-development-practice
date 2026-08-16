<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_displayed(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('auth.index');
    }

    public function test_guest_is_redirected_to_login_page_from_top_page(): void
    {
        $response = $this->get('/top');

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/top');
    }

    public function test_user_is_redirected_to_intended_page_after_login(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->get('/top');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/top');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->from('/')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_view_top_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/top');

        $response->assertOk();
        $response->assertViewIs('top');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
