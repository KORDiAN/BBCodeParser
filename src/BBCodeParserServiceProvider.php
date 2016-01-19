<?php namespace Golonka\BBCode;

use Illuminate\Support\ServiceProvider;

class BBCodeParserServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/bbcode.php' => config_path('bbcode.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bbcode.php', 'bbcode');

        $this->app->bind('bbcode', function () {
            return new BBCodeParser;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['bbcode'];
    }
}
