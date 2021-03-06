<?php
/**
 * LMAOServiceProvider.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 2/18/17
 * Time: 4:45 PM
 *
 *
 *
 */
namespace Topster21\LMAO;

use Illuminate\Support\ServiceProvider;

class LMAOServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require(__DIR__ . '/routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'lmao');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('topster21-lmaoclient', function() {
            return new LMAOClient();
        });
    }
}
