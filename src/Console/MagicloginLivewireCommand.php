<?php

namespace Digitalnode\MagicloginLivewire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MagicloginLivewireCommand extends Command
{
    public $signature = 'magiclogin:install';

    public $description = 'Install the MagicmkAuthLaravelInertia package';

    public function handle(): int
    {
        $this->installMigration();
        $this->installPhoneMigration();
        $this->installMagicAuthController();
        $this->installWebRoutes();
        $this->installVuePage();
        $this->installIntegrationScript();
        $this->publishCustomUpdateUserPasswordAction();
        $this->configureFortifyToUseCustomAction();
        $this->updateEnvFile();
        $this->info('MagicmkAuthLaravelInertia installed successfully.');
        $this->error('Remember to add the project id (slug) and project api key from your magic mk project to your .env');

        return self::SUCCESS;
    }

    protected function installWebRoutes(): void
    {
        $webRoutesPath = base_path('routes/web.php');
        $routeStubPath = __DIR__ . '/../stubs/web.stub';

        if (!File::exists($routeStubPath)) {
            $this->error("Route stub file not found at $routeStubPath");
            return;
        }

        if (!File::exists($webRoutesPath)) {
            $this->error("Web routes file not found at $webRoutesPath");
            return;
        }

        $routeToAdd = File::get($routeStubPath);

        if (Str::contains(File::get($webRoutesPath), $routeToAdd)) {
            $this->warn('Magic login route is already present in web.php.');
            return;
        }

        File::append($webRoutesPath, "\n" . $routeToAdd);
        $this->info('Magic login route added to web.php.');
    }

    protected function installMigration(): void
    {
        $sourcePath = __DIR__ . '/../../database/migrations/make_name_password_email_nullable_in_users_table.php';
        $migrationDir = database_path('migrations');
        $newMigrationName = $migrationDir . '/' . date('Y_m_d_His') . '_make_name_and_password_nullable_in_users_table.php';

        if (!File::exists($sourcePath)) {
            $this->error("Migration source file not found at $sourcePath");
            return;
        }

        if (!File::exists($migrationDir)) {
            $this->error("Migration directory not found at $migrationDir");
            return;
        }

        if (File::exists($newMigrationName)) {
            $this->warn("Migration file $newMigrationName already exists.");
            return;
        }

        File::copy($sourcePath, $newMigrationName);
        $this->info('make_name_password_email_nullable_in_users_table migration installed.');
    }

    protected function installPhoneMigration(): void
    {
        $sourcePath = __DIR__ . '/../../database/migrations/add_phone_to_users_table.php';
        $migrationDir = database_path('migrations');
        $newMigrationName = $migrationDir . '/' . date('Y_m_d_His') . '_add_phone_to_users_table.php';

        if (!File::exists($sourcePath)) {
            $this->error("Migration source file not found at $sourcePath");
            return;
        }

        if (!File::exists($migrationDir)) {
            $this->error("Migration directory not found at $migrationDir");
            return;
        }

        if (File::exists($newMigrationName)) {
            $this->warn("Migration file $newMigrationName already exists.");
            return;
        }

        File::copy($sourcePath, $newMigrationName);
        $this->info('add_phone_to_users_table migration installed.');
    }

    protected function installMagicAuthController(): void
    {
        $sourcePath = __DIR__ . '/../stubs/MagicAuthController.stub';
        $controllerPath = app_path('Http/Controllers/MagicAuthController.php');

        if (!File::exists($sourcePath)) {
            $this->error("MagicAuthController stub not found at $sourcePath");
            return;
        }

        $controllerDir = dirname($controllerPath);
        if (!File::exists($controllerDir)) {
            $this->error("Controllers directory not found at $controllerDir");
            return;
        }

        if (File::exists($controllerPath)) {
            $this->warn("MagicAuthController already exists at $controllerPath.");
            return;
        }

        File::copy($sourcePath, $controllerPath);
        $this->info('MagicAuthController installed.');
    }

    protected function installVuePage(): void
    {
        $sourcePath = __DIR__ . '/../../resources/views/auth/magic-auth.blade.php';
        $bladePath = resource_path('views/auth/magic-auth.blade.php');

        if (!File::exists($sourcePath)) {
            $this->error("Blade template not found at $sourcePath");
            return;
        }

        $bladeDir = dirname($bladePath);
        if (!File::exists($bladeDir)) {
            File::makeDirectory($bladeDir, 0755, true);
            $this->info("Created directory $bladeDir");
        }

        if (File::exists($bladePath)) {
            $this->warn("Blade template already exists at $bladePath.");
            return;
        }

        File::copy($sourcePath, $bladePath);
        $this->info('MagicAuth blade template installed.');
    }

    protected function installIntegrationScript(): void
    {
        $sourcePath = __DIR__ . '/../../resources/js/magicmk_integration_ES6.js';

        $destinationPath = public_path('magicmk_integration_ES6.js');

        if (!File::exists($sourcePath)) {
            $this->error("Integration script not found at $sourcePath");
            return;
        }

        if (File::exists($destinationPath)) {
            $this->warn("Integration script already exists at $destinationPath.");
            return;
        }

        File::copy($sourcePath, $destinationPath);
        $this->info('magicmk_integration_ES6.js script installed.');
    }

    protected function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            $envContent = File::get($envPath);

            if (Str::contains($envContent, 'MAGIC_LOGIN_PROJECT_KEY') ||
                Str::contains($envContent, 'MAGIC_LOGIN_API_KEY') ||
                Str::contains($envContent, 'ALLOW_MAGIC_REGISTERING_USERS')
            ) {
                $this->warn('.env file already contains MAGIC_LOGIN_PROJECT_KEY and/or MAGIC_LOGIN_API_KEY and/or ALLOW_MAGIC_REGISTERING_USERS.');
                return;
            }

            File::append($envPath, "\nMAGIC_LOGIN_PROJECT_KEY=\"\"\nMAGIC_LOGIN_API_KEY=\"\"\nALLOW_MAGIC_REGISTERING_USERS=\"\"\n");
            $this->info('.env file updated.');
        } else {
            $this->error('.env file not found.');
        }
    }

    protected function publishCustomUpdateUserPasswordAction(): void
    {
        $sourcePath = __DIR__ . '/../stubs/MagicLoginUpdateUserPassword.stub';
        $destinationPath = app_path('Actions/Fortify/MagicLoginUpdateUserPassword.php');
        $actionsFolder = app_path('Actions/Fortify');

        if (!File::exists($actionsFolder)) {
            $this->warn("The /app/Actions/Fortify folder does not exist. Ignoring custom user action installation.");
            return;
        }

        if (!File::exists($sourcePath)) {
            $this->error("Stub file not found at $sourcePath");
            return;
        }

        $destinationDir = dirname($destinationPath);
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
            $this->info("Created directory $destinationDir");
        }

        if (File::exists($destinationPath)) {
            $this->warn('Custom MagicLoginUpdateUserPassword action already exists.');
            return;
        }

        File::copy($sourcePath, $destinationPath);

        $this->info('Custom MagicLoginUpdateUserPassword action published.');
    }

    protected function configureFortifyToUseCustomAction(): void
    {
        $providerPath = app_path('Providers/FortifyServiceProvider.php');

        if (!File::exists($providerPath)) {
            $this->warn('FortifyServiceProvider.php not found. Ignoring custom user action installation.');
            return;
        }

        $fileContents = File::get($providerPath);

        $replacementBinding = 'Fortify::updateUserPasswordsUsing(\\App\\Actions\\Fortify\\MagicLoginUpdateUserPassword::class);';

        $pattern = '/Fortify::updateUserPasswordsUsing\([^)]+\);/';

        if (Str::contains($fileContents, $replacementBinding)) {
            $this->info('FortifyServiceProvider.php already configured to use MagicLoginUpdateUserPassword.');
            return;
        }

        if (preg_match($pattern, $fileContents)) {
            $fileContents = preg_replace($pattern, $replacementBinding, $fileContents, 1, $count);

            if ($count > 0) {
                File::put($providerPath, $fileContents);
                $this->info('Updated Fortify::updateUserPasswordsUsing binding in FortifyServiceProvider.php.');
            } else {
                $this->error('Failed to update Fortify::updateUserPasswordsUsing binding.');
                return;
            }
        } else {
            $this->error('Could not find existing Fortify::updateUserPasswordsUsing binding to replace.');
            return;
        }

        $this->info('Fortify configured to use custom UpdateUserPassword action.');
    }
}
