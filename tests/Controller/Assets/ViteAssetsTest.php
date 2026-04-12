<?php

namespace Tests\Controller\Assets;

use App\Settings\SystemSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ViteAssetsTest extends TestCase
{
    use RefreshDatabase;

    private string $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manifestPath = public_path('build/manifest.json');
        File::ensureDirectoryExists(dirname($this->manifestPath));
        File::put($this->manifestPath, json_encode([
            'resources/assets/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/assets/js/app.js',
                'isEntry' => true,
            ],
            'resources/assets/sass/app.scss' => [
                'file' => 'assets/app.css',
                'src' => 'resources/assets/sass/app.scss',
                'isEntry' => true,
            ],
            'resources/assets/sass/app-dark.scss' => [
                'file' => 'assets/app-dark.css',
                'src' => 'resources/assets/sass/app-dark.scss',
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        File::delete($this->manifestPath);
        File::deleteDirectory(public_path('build'));

        parent::tearDown();
    }

    public function test_dashboard_uses_vite_assets(): void
    {
        $user = User::factory()->create(['name' => 'MrTestUser']);

        $this->actingAs($user);

        $response = $this->get('dashboard');

        $response->assertOk()
            ->assertSee('/build/assets/app.css', false)
            ->assertSee('/build/assets/app.js', false);
    }

    public function test_setup_view_uses_vite_assets(): void
    {
        SystemSettings::fake(['setup_completed' => false]);

        $response = $this->get('setup/start');

        $response->assertOk()
            ->assertSee('/build/assets/app.css', false)
            ->assertSee('/build/assets/app.js', false);
    }
}
