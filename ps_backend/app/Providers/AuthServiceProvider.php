<?php
namespace App\Providers;

use Auth;
use Layer;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {   
        // we will use special model for Auth::user
        Auth::provider('model', function($app, array $config) {

            return Layer::model('Muserprovider');
            
        });
    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}