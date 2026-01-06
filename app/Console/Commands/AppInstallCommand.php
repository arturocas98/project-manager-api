<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppInstallCommand extends Command
{
    protected $signature = 'app:install {--fresh : Indicates whether the database should be refreshed}';

    protected $description = 'Execute the initial commands to configure/launch the application.';

    public function handle(): void
    {
        if (blank(config('app.key'))) {
            $this->call('key:generate');
        }

        $this->callSilently('passport:keys');

        $this->option('fresh')
            ? $this->call('migrate:fresh')
            : $this->call('migrate', ['--force' => true]);

        $this->call('db:seed', [
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'laravel-assets',
            '--force' => true,
            '--ansi' => true,
        ]);

        if (app()->isLocal()) {
            $this->call('scribe:generate');
        }

        $this->info('✔ The application has been successfully launched!');
    }
}
