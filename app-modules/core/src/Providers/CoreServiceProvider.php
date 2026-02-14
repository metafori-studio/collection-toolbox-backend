<?php

namespace Metafori\Core\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Locale;
use Metafori\Core\CorePlugin;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(CorePlugin::make());
        });
    }

    public function boot(): void
    {
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
    }
}
