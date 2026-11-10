<?php

namespace App\Providers;

use App\Contracts\MarketplacePaymentProvider;
use App\Contracts\WifiGateway;
use App\Models\User;
use App\Services\MikrotikWifiGateway;
use App\Services\SimulatedMarketplacePaymentProvider;
use App\Services\SimulatedWifiGateway;
use App\Services\XenditMarketplacePaymentProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WifiGateway::class, function (): WifiGateway {
            return match (config('wifi.gateway')) {
                'simulated' => new SimulatedWifiGateway,
                'mikrotik' => new MikrotikWifiGateway,
                default => throw new \InvalidArgumentException('WIFI_GATEWAY must be either simulated or mikrotik.'),
            };
        });
        $this->app->bind(MarketplacePaymentProvider::class, function (): MarketplacePaymentProvider {
            return match (config('services.xendit.mode')) {
                'test', 'live' => new XenditMarketplacePaymentProvider,
                'simulated' => new SimulatedMarketplacePaymentProvider,
                default => throw new \InvalidArgumentException('MARKETPLACE_PAYMENT_MODE must be simulated, test, or live.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $identity = Str::lower(Str::transliterate(trim((string) $request->input('login'))));

            return Limit::perMinute(5)->by($identity.'|'.$request->ip());
        });

        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perHour(5)->by($request->ip()));

        RateLimiter::for('password-reset', function (Request $request): Limit {
            $identity = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(3)->by($identity.'|'.$request->ip());
        });

        RateLimiter::for('verification-resend', function (Request $request): Limit {
            $target = $request->route('user');
            $targetId = $target instanceof User ? $target->getKey() : (string) $target;
            $actorId = $request->user()?->getAuthIdentifier() ?? 'guest';

            return Limit::perHour(3)->by('verification-resend:'.$actorId.':'.$targetId);
        });

        RateLimiter::for('verification-resend-public', function (Request $request): array {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perHour(3)->by('verification-email:'.hash('sha256', $email.'|'.$request->ip())),
                Limit::perHour(10)->by('verification-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('wifi-session', fn (Request $request): Limit => Limit::perMinute(3)->by('wifi:'.$request->user()?->getAuthIdentifier().':'.$request->ip()));
        RateLimiter::for('service-request', fn (Request $request): Limit => Limit::perHour(10)->by('service:'.$request->user()?->getAuthIdentifier()));
    }
}
