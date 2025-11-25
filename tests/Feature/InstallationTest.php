<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;



    public function test_can_view_database_setup_page()
    {
        // Ensure we're in a state where database setup is needed
        // Clear any existing migrations
        try {
            \Illuminate\Support\Facades\Schema::dropIfExists('migrations');
        } catch (\Exception $e) {
            // Ignore
        }

        $response = $this->withoutMiddleware()->get(route('install.step1'));

        $response->assertStatus(200);
        $response->assertSee('Database Configuration');
    }

    public function test_installation_lock_prevents_re_installation()
    {
        // Create installation lock file
        $lockFile = storage_path('app/.installed');
        \Illuminate\Support\Facades\File::put($lockFile, now()->toDateTimeString());

        // Create a user and company to simulate installed state
        User::factory()->create();
        Company::create(['name' => 'Test Company', 'slug' => 'test-company', 'status' => 1]);

        // Try to access install routes - should redirect to home
        $response = $this->get(route('install.step4'));
        $response->assertRedirect(route('home'));

        $response = $this->get(route('install.step4'));
        $response->assertRedirect(route('home'));

        // Clean up
        \Illuminate\Support\Facades\File::delete($lockFile);
    }

    public function test_installation_lock_created_after_company_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lockFile = storage_path('app/.installed');

        // Ensure lock file doesn't exist
        if (\Illuminate\Support\Facades\File::exists($lockFile)) {
            \Illuminate\Support\Facades\File::delete($lockFile);
        }

        $this->assertFalse(\Illuminate\Support\Facades\File::exists($lockFile));

        $this->post(route('install.step4.store'), [
            'name' => 'Test Company',
        ]);

        $this->assertTrue(\Illuminate\Support\Facades\File::exists($lockFile));

        // Clean up
        \Illuminate\Support\Facades\File::delete($lockFile);
    }

    public function test_env_editor_copies_env_bak_to_env()
    {
        $envPath = base_path('.env');
        $envBakPath = base_path('.env.bak');

        // Backup existing .env if it exists
        $envBackup = null;
        if (\Illuminate\Support\Facades\File::exists($envPath)) {
            $envBackup = \Illuminate\Support\Facades\File::get($envPath);
            \Illuminate\Support\Facades\File::delete($envPath);
        }

        // Create .env.bak if it doesn't exist
        if (! \Illuminate\Support\Facades\File::exists($envBakPath)) {
            \Illuminate\Support\Facades\File::put($envBakPath, 'APP_NAME=Test');
        }

        $envBakContent = \Illuminate\Support\Facades\File::get($envBakPath);

        // Test ensureExists
        \App\Support\EnvEditor::ensureExists();

        $this->assertTrue(\Illuminate\Support\Facades\File::exists($envPath));
        $envContent = \Illuminate\Support\Facades\File::get($envPath);

        // The ensureExists method modifies SESSION_DRIVER to 'file', so we check that it exists
        // and that the content is similar (SESSION_DRIVER may be changed)
        $this->assertStringContainsString('APP_NAME', $envContent);
        // Check that SESSION_DRIVER is set to file (may have been modified)
        $this->assertStringContainsString('SESSION_DRIVER=file', $envContent);

        // Restore original .env if it existed
        if ($envBackup !== null) {
            \Illuminate\Support\Facades\File::put($envPath, $envBackup);
        } else {
            \Illuminate\Support\Facades\File::delete($envPath);
        }
    }
}
