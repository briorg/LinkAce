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
    private array $manifest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manifestPath = public_path('build/manifest.json');
        File::ensureDirectoryExists(dirname($this->manifestPath));
        $this->manifest = [
            'resources/assets/js/app.js' => [
                'file' => 'assets/app-abc123.js',
                'src' => 'resources/assets/js/app.js',
                'isEntry' => true,
            ],
            'resources/assets/sass/app.scss' => [
                'file' => 'assets/app-def456.css',
                'src' => 'resources/assets/sass/app.scss',
                'isEntry' => true,
            ],
            'resources/assets/sass/app-dark.scss' => [
                'file' => 'assets/app-dark-ghi789.css',
                'src' => 'resources/assets/sass/app-dark.scss',
                'isEntry' => true,
            ],
        ];

        File::put($this->manifestPath, json_encode($this->manifest, JSON_THROW_ON_ERROR));
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
            ->assertSee('/build/'.$this->manifest['resources/assets/sass/app.scss']['file'], false)
            ->assertSee('/build/'.$this->manifest['resources/assets/js/app.js']['file'], false);
    }

    public function test_setup_view_uses_vite_assets(): void
    {
        SystemSettings::fake(['setup_completed' => false]);

        $response = $this->get('setup/start');

        $response->assertOk()
            ->assertSee('/build/'.$this->manifest['resources/assets/sass/app.scss']['file'], false)
            ->assertSee('/build/'.$this->manifest['resources/assets/js/app.js']['file'], false);
    }
}
