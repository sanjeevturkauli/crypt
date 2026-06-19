<?php

declare(strict_types=1);

namespace SecureCrypto\Encryption\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateEncryptionKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypt:keys 
                            {--show : Display the keys instead of modifying files}
                            {--force : Force the operation to run in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate encryption keys for Response Crypt package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->laravel->environment('production') && !$this->option('force')) {
            $this->components->warn('Use the --force option to generate keys in production.');
            return self::FAILURE;
        }

        // Generate keys
        $key = base64_encode(random_bytes(32));
        $iv = base64_encode(random_bytes(16));

        // Show only option
        if ($this->option('show')) {
            $this->displayKeys($key, $iv);
            return self::SUCCESS;
        }

        // Save to .env file
        $this->saveKeysToEnv($key, $iv);

        $this->components->info('Encryption keys generated successfully!');
        $this->newLine();
        $this->displayKeys($key, $iv, false);
        $this->newLine();
        $this->components->info('Keys have been added to your .env file');

        return self::SUCCESS;
    }

    /**
     * Display generated keys.
     */
    protected function displayKeys(string $key, string $iv, bool $showHeader = true): void
    {
        if ($showHeader) {
            $this->components->info('Generated Encryption Keys:');
            $this->newLine();
        }

        $this->components->twoColumnDetail(
            '<fg=green>CRYPT_KEY</>',
            $this->truncateKey($key)
        );

        $this->components->twoColumnDetail(
            '<fg=green>CRYPT_IV</>',
            $this->truncateKey($iv)
        );
    }

    /**
     * Save keys to .env file.
     */
    protected function saveKeysToEnv(string $key, string $iv): void
    {
        $envPath = $this->laravel->environmentFilePath();

        if (!File::exists($envPath)) {
            File::put($envPath, '');
        }

        $envContent = File::get($envPath);

        // Check if keys already exist
        $hasKey = $this->hasEnvironmentKey($envContent, 'CRYPT_KEY');
        $hasIV = $this->hasEnvironmentKey($envContent, 'CRYPT_IV');

        if ($hasKey || $hasIV) {
            if (!$this->confirm('Keys already exist. Do you want to overwrite them?', false)) {
                $this->components->info('Key generation cancelled.');
                exit(0);
            }

            // Replace existing keys
            if ($hasKey) {
                $envContent = preg_replace(
                    '/^CRYPT_KEY=.*$/m',
                    'CRYPT_KEY="' . $key . '"',
                    $envContent
                );
            }

            if ($hasIV) {
                $envContent = preg_replace(
                    '/^CRYPT_IV=.*$/m',
                    'CRYPT_IV="' . $iv . '"',
                    $envContent
                );
            }
        } else {
            // Append new keys
            $envContent = rtrim($envContent);
            $envContent .= "\n\n# Encryption Keys\n";
            $envContent .= 'CRYPT_KEY="' . $key . '"' . PHP_EOL;
            $envContent .= 'CRYPT_IV="' . $iv . '"' . PHP_EOL;
        }

        File::put($envPath, $envContent);
    }

    /**
     * Check if environment key exists.
     */
    protected function hasEnvironmentKey(string $content, string $key): bool
    {
        return (bool) preg_match("/^{$key}=/m", $content);
    }

    /**
     * Truncate key for display.
     */
    protected function truncateKey(string $key): string
    {
        if (strlen($key) > 40) {
            return substr($key, 0, 20) . '...' . substr($key, -17);
        }

        return $key;
    }
}
