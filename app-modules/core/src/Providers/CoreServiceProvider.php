<?php

namespace Metafori\Core\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Closure;
use Faker\Generator;
use Filament\Forms\Components\Field;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Locale;
use Metafori\Core\Auth\Passwords\PasswordBrokerManager;
use Metafori\Core\CorePlugin;
use Metafori\Core\Facades\Frontend as FrontendFacade;
use Metafori\Core\Faker\Providers\OrcidProvider;
use Metafori\Core\Faker\Providers\RorIdProvider;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Permission;
use Metafori\Core\Models\Region;
use Metafori\Core\Models\Role;
use Metafori\Core\Notifications\SetPassword;
use Metafori\Core\Support\Frontend;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config(['permission.models.permission' => Permission::class]);
        config(['permission.models.role' => Role::class]);

        $this->app->extend(Generator::class, function (Generator $faker) {
            $faker->addProvider(new RorIdProvider($faker));
            $faker->addProvider(new OrcidProvider($faker));

            return $faker;
        });

        $this->mergeConfigFrom(__DIR__.'/../../config/frontend.php', 'frontend');

        $this->app->singleton('frontend', fn () => new Frontend);
        $this->app->extend('auth.password', fn ($_service, Application $app) => new PasswordBrokerManager($app));

        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(CorePlugin::make());
        });
    }

    public function boot(): void
    {
        Route::prependMiddlewareToGroup('api', EnsureFrontendRequestsAreStateful::class);

        ResetPassword::createUrlUsing(FrontendFacade::resetPasswordUrl(...));
        SetPassword::createUrlUsing(FrontendFacade::setPasswordUrl(...));

        TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
            $locales = config('app.locales');
            $currentLocale = app()->getLocale();

            $labels = collect($locales)
                ->mapWithKeys(fn ($locale) => [
                    $locale => Locale::getDisplayName($locale, $currentLocale),
                ])
                ->all();

            $component
                ->localesLabels($labels)
                ->locales($locales);
        });

        TranslatableTabs::macro('requiredOnFallbackLocale', function (bool|Closure $condition = true) {
            /** @var TranslatableTabs $this */
            return $this->modifyFieldsUsing(function (Field $component, string $locale) use ($condition) {
                $component->required(fn () => $component->evaluate($condition) && $locale === config('app.fallback_locale'));
            });
        });

        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'core');

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password.forgot', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password.reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password.set', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        Relation::morphMap([
            'country' => Country::class,
            'region' => Region::class,
            'district' => District::class,
            'municipality' => Municipality::class,
            'municipality_part' => MunicipalityPart::class,
            'location' => Location::class,
        ]);
    }
}
