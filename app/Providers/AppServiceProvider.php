<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Payments\PaymentGateway;
use App\Payments\SignedWebhookGateway;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, SignedWebhookGateway::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn ($user, $ability) => $user->hasRole('super-admin') ? true : null);
        foreach ([Login::class => 'auth.login', Logout::class => 'auth.logout'] as $event => $action) {
            Event::listen($event, fn ($event) => AuditLog::log($action, $event->user));
        }
    }
}
