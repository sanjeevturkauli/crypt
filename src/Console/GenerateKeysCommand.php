<?php

declare(strict_types=1);

namespace Sanjeev\ResponseCrypt\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'response-crypt:generate-keys 
                            {--show : Display the keys instead of modifying files}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     */
    protected $description = 'Generate encryption key and IV for Response Crypt package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Generate 32-byte key for AES-256
        $key = random_bytes(32);
        $keyBase64 = base64_encode($key);

        // Generate 16-byte IV for AES-256-CBC
        $iv = random_bytes(16);
        $ivBase64 = base64_encode($iv);

        if ($this->option('show')) {
            $this->line('<comment>Encryption Key:</comment> ' . $keyBase64);
            $this->line('<comment>Encryption IV:</comment> ' . $ivBase64);
            $this->newLine();
            $this->info('Add these to your .env file:');
            $this->line('RESPONSE_CRYPT_KEY="' . $keyBase64 . '"');
            $this->line('RESPONSE_CRYPT_IV="' . $ivBase64 . '"');
            return 0;
        }

        if (!$this->confirmToProceed()) {
            return 1;
        }

        $this->writeToEnvironmentFile($keyBase64, $ivBase64);

        $this->info('Encryption keys generated successfully!');
        $this->line('RESPONSE_CRYPT_KEY="' . $keyBase64 . '"');
        $this->line('RESPONSE_CRYPT_IV="' . $ivBase64 . '"');

        return 0;
    }

    /**
     * Write the encryption keys to the environment file.
     */
    protected function writeToEnvironmentFile(string $key, string $iv): void
    {
        $envPath = $this->laravel->environmentFilePath();
        $envContent = file_get_contents($envPath);

        // Update or add RESPONSE_CRYPT_KEY
        if (preg_match('/^RESPONSE_CRYPT_KEY=/m', $envContent)) {
            $envContent = preg_replace(
                '/^RESPONSE_CRYPT_KEY=.*/m',
                'RESPONSE_CRYPT_KEY="' . $key . '"',
                $envContent
            );
        } else {
            $envContent .= "\n# Response Crypt Package Keys\n";
            $envContent .= 'RESPONSE_CRYPT_KEY="' . $key . '"' . "\n";
        }

        // Update or add RESPONSE_CRYPT_IV
        if (preg_match('/^RESPONSE_CRYPT_IV=/m', $envContent)) {
            $envContent = preg_replace(
                '/^RESPONSE_CRYPT_IV=.*/m',
                'RESPONSE_CRYPT_IV="' . $iv . '"',
                $envContent
            );
        } else {
            $envContent .= 'RESPONSE_CRYPT_IV="' . $iv . '"' . "\n";
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Confirm before proceeding with the action.
     */
    protected function confirmToProceed(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if ($this->laravel->environment('production')) {
            $this->alert('Warning: You are in production environment!');
        }

        return $this->confirm('Do you want to generate new encryption keys?');
    }
}
