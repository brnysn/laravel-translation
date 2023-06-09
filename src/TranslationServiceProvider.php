<?php

namespace Brnysn\Translation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Brnysn\Translation\Console\Commands\AddLanguageCommand;
use Brnysn\Translation\Console\Commands\AddTranslationKeyCommand;
use Brnysn\Translation\Console\Commands\ListLanguagesCommand;
use Brnysn\Translation\Console\Commands\ListMissingTranslationKeys;
use Brnysn\Translation\Console\Commands\SynchroniseMissingTranslationKeys;
use Brnysn\Translation\Console\Commands\SynchroniseTranslationsCommand;
use Brnysn\Translation\Drivers\Translation;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViews();

        $this->registerRoutes();

        $this->publishConfiguration();

        $this->publishAssets();

        $this->loadMigrations();

        $this->loadTranslations();

        $this->registerHelpers();
    }

    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfiguration();

        $this->registerCommands();

        $this->registerContainerBindings();
    }

    /**
     * Load and publish package views.
     *
     * @return void
     */
    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'translation');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/translation'),
        ]);
    }

    /**
     * Register package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group(config('translation.api_route_group_config') + ['namespace' => 'Brnysn\\Translation\\Http\\Controllers\\Api'] + ['prefix' => config('translation.api_url')], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
        Route::group(config('translation.route_group_config') + ['namespace' => 'Brnysn\\Translation\\Http\\Controllers'] + ['prefix' => config('translation.ui_url')], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Publish package configuration.
     *
     * @return void
     */
    private function publishConfiguration()
    {
        $this->publishes([
            __DIR__.'/../config/translation.php' => config_path('translation.php'),
        ], 'config');
    }

    /**
     * Merge package configuration.
     *
     * @return void
     */
    private function mergeConfiguration()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translation.php', 'translation');
    }

    /**
     * Publish package assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../public/assets' => public_path('vendor/translation'),
        ], 'assets');
    }

    /**
     * Load package migrations.
     *
     * @return void
     */
    private function loadMigrations()
    {
        if (config('translation.driver') !== 'database') {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Load package translations.
     *
     * @return void
     */
    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'translation');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/translation'),
        ]);
    }

    /**
     * Register package commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddLanguageCommand::class,
                AddTranslationKeyCommand::class,
                ListLanguagesCommand::class,
                ListMissingTranslationKeys::class,
                SynchroniseMissingTranslationKeys::class,
                SynchroniseTranslationsCommand::class,
            ]);
        }
    }

    /**
     * Register package helper functions.
     *
     * @return void
     */
    private function registerHelpers()
    {
        require __DIR__.'/../resources/helpers.php';
    }

    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    private function registerContainerBindings()
    {
        $this->app->singleton(Scanner::class, function () {
            $config = $this->app['config']['translation'];

            return new Scanner(new Filesystem(), $config['scan_paths'], $config['translation_methods']);
        });

        $this->app->singleton(Translation::class, function ($app) {
            return (new TranslationManager($app, $app['config']['translation'], $app->make(Scanner::class)))->resolve();
        });
    }
}
