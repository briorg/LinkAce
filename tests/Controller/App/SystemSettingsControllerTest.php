<?php

namespace Tests\Controller\App;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->user->assignRole(Role::ADMIN);
    }

    public function test_settings_access_for_users(): void
    {
        // No access for regular users
        $this->user->syncRoles(Role::USER);

        $response = $this->get('settings/system');
        $response->assertForbidden();

        // Access granted for admins
        $this->user->syncRoles(Role::ADMIN);

        $response = $this->get('settings/system');
        $response->assertOk();
    }

    public function test_valid_settings_response(): void
    {
        $response = $this->get('settings/system');

        $response->assertOk()
            ->assertSee('Cron Token')
            ->assertSee('System Settings');
    }

    public function test_valid_settings_update_response(): void
    {
        $response = $this->get('dashboard');
        $response->assertDontSee('Begin of custom header scripts');

        $response = $this->post('settings/system', [
            'page_title' => 'New HTML Title',
            'logo_text' => 'Meine Bookmarks',
            'additional_footer_link_url' => 'https://woblick.dev',
            'additional_footer_link_text' => 'Portfolio',
            'contact_page_enabled' => '1',
            'contact_page_title' => 'ContactPage',
            'contact_page_content' => '**Example** with [link](https://woblick.dev)',
            'custom_header_content' => '<script>console.log(\'scripts work\')</script>',
        ]);

        $response->assertRedirect('settings/system');

        $this->assertEquals('New HTML Title', systemsettings('page_title'));
        $this->assertEquals('Meine Bookmarks', systemsettings('logo_text'));
        $this->assertEquals('https://woblick.dev', systemsettings('additional_footer_link_url'));
        $this->assertEquals('Portfolio', systemsettings('additional_footer_link_text'));
        $this->assertTrue(systemsettings('contact_page_enabled'));

        $this->get('contact')
            ->assertSee('<script>console.log(\'scripts work\')</script>', false)
            ->assertSee('New HTML Title')
            ->assertSee('Meine Bookmarks')
            ->assertSee('Portfolio')
            ->assertSee('ContactPage')
            ->assertSee('<strong>Example</strong> with <a href="https://woblick.dev">link</a>', false);
    }

    public function test_valid_guest_settings_update_response(): void
    {
        $response = $this->get('dashboard');
        $response->assertDontSee('Begin of custom header scripts');

        $response = $this->post('settings/system/guest', [
            'guest_access_enabled' => '1',
            'locale' => 'de_DE',
        ]);

        $response->assertRedirect('settings/system');

        $this->assertTrue(systemsettings('guest_access_enabled'));
        $this->assertEquals('de_DE', guestsettings('locale'));

        auth()->logout();
        $response = $this->get('guest/links');
        $response->assertSee('Listen');
    }

    public function test_valid_cron_generaton_response(): void
    {
        $response = $this->post('settings/generate-cron-token');

        $response->assertOk()
            ->assertJsonStructure([
                'new_token',
            ]);

        $this->assertNotNull(systemsettings('cron_token'));
    }
}
