<?php

namespace Tests\Controller\App;

use App\Enums\ModelAttribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_valid_settings_response(): void
    {
        $response = $this->get('settings');

        $response->assertOk()
            ->assertSee('Bookmarklet')
            ->assertSee('API Token')
            ->assertSee('Account Settings')
            ->assertSee('Change Password')
            ->assertSee('User Settings');
    }

    public function test_valid_update_account_settings_response(): void
    {
        $response = $this->post('settings/account', [
            'name' => 'NewName',
            'email' => 'test@linkace.org',
        ])->assertSessionHasNoErrors();

        $response->assertRedirect('/');

        $updatedUser = User::notSystem()->first();

        $this->assertEquals('NewName', $updatedUser->name);
        $this->assertEquals('test@linkace.org', $updatedUser->email);
    }

    public function test_valid_update_application_settings_response(): void
    {
        $response = $this->post('settings/app', [
            'locale' => 'en_US',
            'timezone' => 'Europe/Berlin',
            'links_default_visibility' => ModelAttribute::VISIBILITY_PRIVATE,
            'notes_default_visibility' => ModelAttribute::VISIBILITY_PRIVATE,
            'lists_default_visibility' => ModelAttribute::VISIBILITY_PRIVATE,
            'tags_default_visibility' => ModelAttribute::VISIBILITY_PRIVATE,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'listitem_count' => '24',
            'link_display_mode' => '1',
            'darkmode_setting' => '0',
        ]);

        $response->assertRedirect('/');

        $this->assertEquals('en_US', usersettings('locale'));
        $this->assertEquals('Europe/Berlin', usersettings('timezone'));
        $this->assertEquals(ModelAttribute::VISIBILITY_PRIVATE, usersettings('links_default_visibility'));
        $this->assertEquals(ModelAttribute::VISIBILITY_PRIVATE, usersettings('notes_default_visibility'));
        $this->assertEquals(ModelAttribute::VISIBILITY_PRIVATE, usersettings('lists_default_visibility'));
        $this->assertEquals(ModelAttribute::VISIBILITY_PRIVATE, usersettings('tags_default_visibility'));
        $this->assertEquals('Y-m-d', usersettings('date_format'));
        $this->assertEquals('H:i', usersettings('time_format'));
        $this->assertEquals(24, usersettings('listitem_count'));
        $this->assertEquals(1, usersettings('link_display_mode'));
        $this->assertEquals(0, usersettings('darkmode_setting'));
    }

    public function test_valid_update_password_response(): void
    {
        $response = $this->post('settings/change-password', [
            'current_password' => 'secretpassword',
            'password' => 'newuserpassword',
            'password_confirmation' => 'newuserpassword',
        ]);

        $response->assertRedirect('/');

        $flashMessage = session('flash_notification', collect())->first();
        $this->assertEquals('Password changed successfully!', $flashMessage['message']);

        $this->assertEquals(true, Auth::attempt([
            'email' => $this->user->email,
            'password' => 'newuserpassword',
        ]));
    }
}
