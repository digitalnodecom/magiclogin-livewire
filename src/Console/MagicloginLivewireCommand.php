<?php

namespace Digitalnode\MagicloginLivewire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MagicloginLivewireCommand extends Command
{
    public $signature = 'magiclogin:install';

    public $description = 'Install the MagicmkAuthLaravelInertia package';

    public function handle(): int
    {
        $this->warn("Will the authentication be via LINK or CODE?");
        $linkSelected = $this->confirm('"yes"-LINK ; "no"-CODE', true);
        $this->installMigration();
        $this->installMagicAuthController();
        $this->installWebRoutes();
        $this->installVuePage();
        $this->installIntegrationScript($linkSelected);
        $this->updateEnvFile();
        $this->info('MagicmkAuthLaravelInertia installed successfully.');
        $this->warn('Remember to add the project id (slug) and project api key from your magic mk project to your .env');

        return self::SUCCESS;
    }

    protected function installWebRoutes(): void
    {
        $webRoutesPath = base_path('routes/web.php');
        $routeStubPath = __DIR__ . '/../stubs/web.stub';

        $routeToAdd = File::get($routeStubPath);
        File::append($webRoutesPath, "\n" . $routeToAdd);
        $this->info('Magic login route added to web.php.');
    }

    protected function installMigration(): void
    {
        $newMigrationName = 'database/migrations/' . date('Y_m_d_His') . '_make_name_and_password_nullable_in_users_table.php';
        File::copy(__DIR__ . '/../../database/migrations/make_name_and_password_nullable_in_users_table.php', $newMigrationName);
        $this->info('make_name_and_password_nullable_in_users_table migration installed.');
    }


    protected function installMagicAuthController(): void
    {
        $controllerPath = app_path('Http/Controllers/MagicAuthController.php');
        File::copy(__DIR__ . '/../stubs/MagicAuthController.stub', $controllerPath);
        $this->info('MagicAuthController installed.');
    }

    protected function installVuePage(): void
    {
        $bladePath = resource_path('views/auth/magic-auth.blade.php');
        File::copy(__DIR__ . '/../../resources/views/auth/magic-auth.blade.php', $bladePath);
        $this->info('MagicAuth blade template installed.');
    }

    protected function installIntegrationScript($linkSelected): void
    {
        if ($linkSelected) {
            $sourcePath = __DIR__ . '/../../resources/js/link-integration/magicmk_integration.js';
        } else {
            $sourcePath = __DIR__ . '/../../resources/js/code-integration/magicmk_integration.js';
        }

        $destinationPath = public_path('magicmk_integration.js');

        File::copy($sourcePath, $destinationPath);
        $this->info('magicmk_integration.js script installed.');
    }

    protected function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            File::append($envPath, "\nMAGIC_LOGIN_PROJECT_KEY=\"\"\nMAGIC_LOGIN_API_KEY=\"\"\n");
            $this->info('.env file updated with MAGIC_LOGIN_PROJECT_KEY and MAGIC_LOGIN_API_KEY.');
        } else {
            $this->error('.env file not found.');
        }
    }
}
