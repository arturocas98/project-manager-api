<?php

namespace App\Providers;

use App\Enums\QueueName;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Health\Checks\Checks;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootstrap();

        $this->rateLimiters();

        $this->oauth();

        $this->morphMapping();

        $this->appHealthCheck();
    }

    /**
     * Set initial settings, such as monitoring in the development environment and disabling commands
     * destructive in the production environment.
     */
    protected function bootstrap(): void
    {
        // Disable destructive commands in production environment to avoid data loss.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        // Ensure strict mode is enabled for all models in development environment.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Log lazy loading violations to the Laravel log for debugging purposes.
        Model::handleLazyLoadingViolationUsing(static function (Model $model, string $relation) {
            $class = get_class($model);

            info("Attempted to lazy load [{$relation}] on model [{$class}].");
        });
    }

    /**
     * Defines rate limiters to use in the application.
     */
    protected function rateLimiters(): void
    {
        // Set up rate limiting for API requests.
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Set OAuth2.0 configurations through Passport.
     */
    protected function oauth(): void
    {
        Passport::loadKeysFrom(storage_path('/oauth'));

        Passport::hashClientSecrets();
    }

    /**
     * Defines a mapping of polymorphic relationships.
     */
    protected function morphMapping(): void
    {
        Relation::enforceMorphMap([
            User::class,
        ]);
    }

    /**
     * Records application health checks.
     */
    protected function appHealthCheck(): void
    {
        Health::checks([
            Checks\DebugModeCheck::new(),
            Checks\EnvironmentCheck::new(),
            // Checks\DatabaseCheck::new(),
            // Checks\RedisCheck::new(),
            // Checks\CacheCheck::new(),
            // Checks\QueueCheck::new()->onQueue([
            //     QueueName::default->value,
            // ]),
            // Checks\ScheduleCheck::new(),
            // Checks\PingCheck::new()->url('https://google.com')->name('Google'),
        ]);
    }
}
