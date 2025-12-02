<?php

namespace Susoft\LicenseGuard;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Susoft\LicenseGuard\Http\Middleware\LicenseGuardMiddleware;
use Susoft\LicenseGuard\Console\InstallCommand;

class LicenseGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/license_guard.php',
            'license_guard'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/license_guard.php' => config_path('license_guard.php'),
        ], 'license-guard-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'license_guard');

        // Đăng ký command khi chạy CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            return;
        }

        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(LicenseGuardMiddleware::class);
    }
}
