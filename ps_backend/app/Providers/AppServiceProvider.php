<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Blade Directives
        |--------------------------------------------------------------------------
        |
        | Here we will register all custom blade directives to help us group all 
        | reusable view logic. Thanks Laravel :) 
        |  
        |
        */
       
        /**
         * This will return the whole section as string
         */
        Blade::directive('section_string', function($section_name) {
            
            return "<?php echo json_encode(app()->view->getSections()[{$section_name}]) ?>";

        });

        /**
         * Shortcut for getting items via Layer::service('Ssession')->get
         */
        Blade::directive('session', function($name) {
            
            return "<?php echo e(Layer::service('Ssession')->get{$name}); ?>";

        });

        /**
         * Shortcut for getting items via Layer::service('Ssession')->get
         */
        Blade::directive('lang_id', function($name) {
            
            return "<?php echo e(Layer::service('Ssiteconfig')->get_lang_id()); ?>";

        });

        /**
         * Get csrf in cookie only
         */
        Blade::directive('csrf', function($name) {

            return "<?php echo json_encode(Cookie::get('ps_token')); ?>";

        });

        /**
         * Shortcut for php include() of any self hosted asset.
         * Via Layer::service('Ssiteconfig')->inline_asset
         */
        Blade::directive('inline_script', function($asset) {

            return "<?php include(Layer::service('Ssiteconfig')->inline_script{$asset}); ?>";

        });

        /**
         * This will get data from config file
         * Via  Layer::service('Ssiteconfig')->get()
         */
        Blade::directive('config', function($config) {

            return "<?php echo json_encode(Layer::service('Ssiteconfig')->get{$config}); ?>";

        });

        /**
         * This will get data from config file
         * Via  Layer::service('Ssiteconfig')->get()
         */
        Blade::directive('rso_full', function($dot_notation) {

            return "<?php echo json_encode(Layer::service('Ssiteconfig')->rso_full{$dot_notation}); ?>";

        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
