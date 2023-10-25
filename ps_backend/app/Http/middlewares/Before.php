<?php 
namespace Backend\middlewares;

use Closure;
use DB;
use Config;
use Request;
use App;
use Auth;
use Redirect;
use Response;
use Layer;
use Exception;
use Cookie;

class Before {
	public function handle($request, Closure $next)
    {
		/*
		|---------------------------------------------------------------------------------------------------------------
		| This before middleware runs under "global" middleware group, all routes will pass here
		|---------------------------------------------------------------------------------------------------------------
		| App global initializations
		*/
	
		# Enable query logging
		if(Config::has('settings.ENABLE_QUERY_LOG') && Config::has('settings.ENABLE_QUERY_LOG')==true) {
			DB::enableQueryLog();
		}

        # Timezone setting from database platform configurations.
		Config::set('timezone', Layer::service('Ssiteconfig')->get_system_time());

		# Set language in cookie
		Layer::service('Ssiteconfig')->init_lang_id();

		# Force HTTPS Connection
		if (!Request::secure() && Config::get('settings.FORCE_HTTPS')!=false) {
			return Redirect::secure(Request::path());
		}

		// CORS
		if (Request::getMethod() == "OPTIONS") {

			$headers = array(
						'Access-Control-Allow-Methods'=> 'POST, GET',
						'Access-Control-Allow-Headers'=> 'X-Requested-With, content-type'
					);

			return Response::make('', 200, $headers);

		}
		
        return $next($request);
    }
}