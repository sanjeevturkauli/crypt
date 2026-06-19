<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secure-crypto:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up all SecureCrypto package files and environment variables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will remove all SecureCrypto configuration files and environment variables. Continue?')) {
                $this->info('Cleanup cancelled.');
                return self::FAILURE;
            }
        }

        $this->info('Starting SecureCrypto package cleanup...');

        // Remove published config file
        $this->removeConfigFile();

        // Remove environment variables
        $this->removeEnvironmentVariables();

        $this->newLine();
        $this->info('✓ SecureCrypto package cleanup completed successfully!');
        $this->newLine();
        $this->comment('The following items have been removed:');
        $this->line('  - Configuration file: config/secure-crypto.php');
        $this->line('  - Environment variables: CRYPT_KEY, CRYPT_IV, CRYPT_DRIVER');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Remove the published configuration file.
     */
    protected function removeConfigFile(): void
    {
        $configPath = config_path('secure-crypto.php');

        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->line('  ✓ Removed config file: config/secure-crypto.php');
        } else {
            $this->line('  ℹ Config file not found (may not have been published)');
        }
    }

    /**
     * Remove environment variables from .env file.
     */
    protected function removeEnvironmentVariables(): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->line('  ℹ .env file not found');
            return;
        }

        $envContent = File::get($envPath);
        $originalContent = $envContent;

        // Keys to remove
        $keysToRemove = [
            'CRYPT_KEY',
            'CRYPT_IV',
            'CRYPT_DRIVER',
        ];

        // Remove each key and its value
        foreach ($keysToRemove as $key) {
            // Pattern matches: KEY=value or KEY="value" (with or without quotes)
            $pattern = "/^{$key}=.*$/m";
            $envContent = preg_replace($pattern, '', $envContent);
        }

        // Remove the comment line if it exists
        $envContent = preg_replace("/^# Encryption Keys\s*$/m", '', $envContent);

        // Clean up multiple consecutive blank lines
        $envContent = preg_replace("/\n{3,}/", "\n\n", $envContent);

        // Trim trailing whitespace
        $envContent = rtrim($envContent) . "\n";

        // Only write if content changed
        if ($envContent !== $originalContent) {
            File::put($envPath, $envContent);
            $this->line('  ✓ Removed environment variables from .env file');
        } else {
            $this->line('  ℹ No environment variables found in .env file');
        }
    }
}
