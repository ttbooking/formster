<?php

declare(strict_types=1);

namespace TTBooking\Formster;

// use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TTBooking\Formster\Contracts\PropertyParser;

class FormsterServiceProvider extends ServiceProvider // implements DeferrableProvider
{
    /**
     * All of the singletons that should be registered.
     *
     * @var array<string, class-string>
     */
    public array $singletons = [
        'property-parser' => PropertyParserManager::class,
        'property-handler' => HandlerFactory::class,
        'action-handler' => ActionHandler::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerResources();
        $this->registerComponents();

        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }
    }

    /**
     * Register the Formster resources.
     */
    protected function registerResources(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'formster');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'formster');
    }

    /**
     * Register Formster's Blade components.
     */
    protected function registerComponents(): void
    {
        Blade::componentNamespace('TTBooking\\Formster\\View\\Components', 'formster');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'formster');
    }

    /**
     * Setup the resource publishing groups for Formster.
     */
    protected function offerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/formster.php' => $this->app->configPath('formster.php'),
        ], ['formster-config', 'formster', 'config']);

        $this->publishes([
            __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/formster'),
        ], ['formster-views', 'formster', 'views']);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configure();
        $this->registerServices();
    }

    /**
     * Setup the configuration for Formster.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/formster.php', 'formster');
    }

    /**
     * Register Formster's services in the container.
     */
    protected function registerServices(): void
    {
        /** @phpstan-ignore-next-line */
        $this->app->singleton('property-parser.driver', static fn ($app) => $app['property-parser']->driver());
        $this->app->alias('property-parser.driver', PropertyParser::class);

        $this->app->when(HandlerFactory::class)->needs('$handlers')->giveConfig('formster.property_handlers', []);
        $this->app->alias('property-handler', Contracts\HandlerFactory::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            'property-parser', 'property-parser.driver', PropertyParser::class,
            'property-handler', Contracts\HandlerFactory::class,
            'action-handler', Contracts\ActionHandler::class,
        ];
    }
}
