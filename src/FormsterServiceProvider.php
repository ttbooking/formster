<?php

declare(strict_types=1);

namespace TTBooking\Formster;

// use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

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
     * The commands to be registered.
     *
     * @var list<class-string<Command>>
     */
    protected array $commands = [
        Console\HandlerMakeCommand::class,
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
        $this->registerCommands();
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
        $this->app->alias('property-parser', Contracts\ParserFactory::class);

        /** @phpstan-ignore-next-line */
        $this->app->singleton('property-parser.parser', static fn ($app) => $app['property-parser']->parser());
        $this->app->alias('property-parser.parser', Contracts\PropertyParser::class);

        $this->app->when(Parsers\CachingParser::class)->needs(Repository::class)->give(
            static fn () => Cache::store(config('formster.property_cache.store')) // @phpstan-ignore argument.type
        );
        $this->app->when(Parsers\CachingParser::class)->needs('$ttl')->giveConfig('formster.property_cache.ttl');
        $this->app->extend(
            Contracts\PropertyParser::class,
            static function (Contracts\PropertyParser $parser, Container $container) {
                return $container->make(Parsers\CachingParser::class, compact('parser'));
            }
        );

        $this->app->when(HandlerFactory::class)->needs('$handlers')->giveConfig('formster.property_handlers', []);
        $this->app->alias('property-handler', Contracts\HandlerFactory::class);
    }

    /**
     * Register the Formster Artisan commands.
     */
    protected function registerCommands(): void
    {
        foreach ($this->commands as $command) {
            $this->app->singleton($command);
        }

        $this->commands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            'property-parser', Contracts\ParserFactory::class,
            'property-parser.parser', Contracts\PropertyParser::class,
            'property-handler', Contracts\HandlerFactory::class,
            'action-handler', Contracts\ActionHandler::class,
            ...$this->commands,
        ];
    }
}
