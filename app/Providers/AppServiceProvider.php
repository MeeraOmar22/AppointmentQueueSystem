<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

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
        // Schedule the feedback link sending command
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Run every 5 minutes to check for appointments completed 1 hour ago
            $schedule->command('feedback:send-links')
                ->everyFiveMinutes()
                ->name('send-feedback-links')
                ->withoutOverlapping();
        });
    }
}
