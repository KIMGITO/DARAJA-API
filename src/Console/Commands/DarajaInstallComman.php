<?php

namespace Codenson\Daraja\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DarajaInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codenson:install
                            {--force : Overwrite any existing files}
                            {--no-migrate : Skip migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure the Daraja M-PESA package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Installing Daraja M-PESA Package...');
        $this->newLine();

        // 1. Publish configuration
        $this->publishConfig();
        
        // 2. Publish migrations
        $this->publishMigrations();
        
        // 3. Run migrations
        if (!$this->option('no-migrate')) {
            $this->runMigrations();
        }
        
        // 4. Update .env file
        $this->updateEnvFile();
        
        // 5. Test configuration
        $this->testConfiguration();
        
        $this->newLine();
        $this->info('✅ Daraja package installed successfully!');
        $this->newLine();
        
        $this->showNextSteps();
    }

    /**
     * Publish configuration file
     */
    protected function publishConfig()
    {
        $this->info('📝 Publishing configuration file...');
        
        $params = [
            '--provider' => 'Codenson\\Daraja\\DarajaServiceProvider',
            '--tag' => 'daraja-config'
        ];
        
        if ($this->option('force')) {
            $params['--force'] = true;
        }
        
        Artisan::call('vendor:publish', $params);
        
        $this->info('✅ Config published to: config/daraja.php');
        $this->newLine();
    }

    /**
     * Publish migrations
     */
    protected function publishMigrations()
    {
        $this->info('📦 Publishing migrations...');
        
        $params = [
            '--provider' => 'Codenson\\Daraja\\DarajaServiceProvider',
            '--tag' => 'daraja-migrations'
        ];
        
        if ($this->option('force')) {
            $params['--force'] = true;
        }
        
        Artisan::call('vendor:publish', $params);
        
        $this->info('✅ Migrations published successfully');
        $this->newLine();
    }

    /**
     * Run migrations
     */
    protected function runMigrations()
    {
        $this->info('🔄 Running migrations...');
        
        if ($this->confirm('Do you want to run migrations now?', true)) {
            Artisan::call('migrate');
            $this->info(Artisan::output());
            $this->info('✅ Migrations completed');
        } else {
            $this->warn('⚠️  Skipped migrations. Run php artisan migrate later.');
        }
        
        $this->newLine();
    }

    /**
     * Update .env file with Daraja configuration
     */
    protected function updateEnvFile()
    {
        $this->info('🔧 Updating .env file...');
        
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            $this->warn('⚠️  .env file not found. Please add configuration manually.');
            return;
        }
        
        $envConfig = [
            'MPESA_ENVIRONMENT' => 'sandbox',
            'MPESA_CONSUMER_KEY' => 'your_consumer_key_here',
            'MPESA_CONSUMER_SECRET' => 'your_consumer_secret_here',
            'MPESA_SHORTCODE' => '174379',
            'MPESA_PASSKEY' => 'your_passkey_here',
            'MPESA_INITIATOR_NAME' => 'your_initiator_name',
            'MPESA_INITIATOR_PASSWORD' => 'your_initiator_password',
            'MPESA_SECURITY_CREDENTIAL' => 'your_security_credential',
            'MPESA_STK_CALLBACK_URL' => 'https://your-domain.com/api/mpesa/stk-callback',
            'MPESA_C2B_CONFIRMATION_URL' => 'https://your-domain.com/api/mpesa/c2b-confirmation',
            'MPESA_C2B_VALIDATION_URL' => 'https://your-domain.com/api/mpesa/c2b-validation',
            'MPESA_B2C_TIMEOUT_URL' => 'https://your-domain.com/api/mpesa/b2c-timeout',
            'MPESA_B2C_RESULT_URL' => 'https://your-domain.com/api/mpesa/b2c-result',
            'MPESA_LOGGING' => 'true',
            'MPESA_TIMEOUT' => '30',
        ];
        
        $envContent = file_get_contents($envPath);
        $needsUpdate = false;
        
        foreach ($envConfig as $key => $value) {
            if (!str_contains($envContent, "$key=")) {
                file_put_contents($envPath, "\n$key=$value", FILE_APPEND);
                $needsUpdate = true;
            }
        }
        
        if ($needsUpdate) {
            $this->info('✅ Environment variables added to .env file');
            $this->warn('⚠️  Please update the values in your .env file with your actual credentials');
        } else {
            $this->info('✅ Environment variables already exist');
        }
        
        $this->newLine();
    }

    /**
     * Test the configuration
     */
    protected function testConfiguration()
    {
        $this->info('🧪 Testing configuration...');
        
        $consumerKey = config('daraja.consumer_key');
        $environment = config('daraja.environment');
        
        if (empty($consumerKey) || $consumerKey === 'your_consumer_key_here') {
            $this->warn('⚠️  Please update your consumer key in .env file');
        } else {
            $this->info('✅ Consumer key configured');
        }
        
        $this->info("✅ Environment set to: {$environment}");
        
        $this->newLine();
    }

    /**
     * Show next steps
     */
    protected function showNextSteps()
    {
        $this->info('📖 Next Steps:');
        $this->newLine();
        
        $this->line('1. Update your .env file with your actual M-PESA credentials:');
        $this->line('   MPESA_CONSUMER_KEY=your_actual_key');
        $this->line('   MPESA_CONSUMER_SECRET=your_actual_secret');
        $this->newLine();
        
        $this->line('2. Update your callback URLs in .env file:');
        $this->line('   MPESA_STK_CALLBACK_URL=https://your-domain.com/api/mpesa/stk-callback');
        $this->newLine();
        
        $this->line('3. Use the package in your controllers:');
        $this->line('   use Codenson\\Daraja\\Facades\\Daraja;');
        $this->line('   $response = Daraja::stkPush()->request([...]);');
        $this->newLine();
        
        $this->line('4. For sandbox testing, use:');
        $this->line('   MPESA_ENVIRONMENT=sandbox');
        $this->line('   MPESA_SHORTCODE=174379');
        $this->newLine();
        
        $this->line('📚 Documentation: https://github.com/KIMGITO/DARAJA-API/tags');
        $this->newLine();
        
        $this->info('🎉 Happy coding!');
    }
}