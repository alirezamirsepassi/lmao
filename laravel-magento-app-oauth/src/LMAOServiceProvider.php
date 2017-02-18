<?php
/**
 * LMAOServiceProvider.php
 *
 * Author: kevin
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('topster21-lmaoclient', function() {
            return new Client();
        });
    }
}
