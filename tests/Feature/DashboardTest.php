<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Routeur;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_displays_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('routeursActifs');
    }

    public function test_routeur_sync_requires_authentication()
    {
        $routeur = Routeur::factory()->create();
        $response = $this->get(route('routeurs.sync', $routeur));
        $response->assertRedirect('/login');
    }
}