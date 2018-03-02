<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
          /**
           * Loader for registering facades.
           */
          $loader = \Illuminate\Foundation\AliasLoader::getInstance();

          /*
           * Load third party local providers
           */
          $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
          $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

          /*
           * Load third party local aliases
           */
          $loader->alias('Debugbar', \Barryvdh\Debugbar\Facade::class);
        }
    }
}
