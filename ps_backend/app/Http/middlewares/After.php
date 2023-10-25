<?php 
namespace Backend\middlewares;

use Closure;
use Cookie;
use Response;
use Auth;
use Request;
use Config;
use URL;
use DB;
use Redirect;
use Exception;
use Layer;

class After {

	public function handle($request, Closure $next)
    {
		/*
		|---------------------------------------------------------------------------------------------------------------
		| AFTER 
		|---------------------------------------------------------------------------------------------------------------
		| Global http reponse modifier
		*/
	
		$response = $next($request);

		// DB Query profiling
		if (Config::has('settings.ENABLE_QUERY_LOG') && Config::get('settings.ENABLE_QUERY_LOG') == true) {

			$allQueries = DB::getQueryLog();
			Layer::service('Slogger')->file(array(
				'timing'  => round((microtime(true) - LARAVEL_START) * 1000,2),
				'isLogin' => Auth::check(),
				'queries' => $allQueries
			),'DB QUERIES', 'ps_sql');

		}

		// update last activity
		if(Auth::check()) {
			$bypass_session_urls = array(URL::to('sc'),URL::to('verify_parent'),URL::to('authenticate'));
			if (!in_array(Request::url(), $bypass_session_urls)) {
				Layer::service('Ssession')->update_lastActivity(Auth::user()->clientID);
			}
		}

		// Set-up 200 response headers and cookies
		header_remove('X-Powered-By');
		$response->header("Pragma", "no-cache");
		$response->header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");

		if (Request::ajax()) {
			
			if (Auth::check()) {
				$response->header('PS-Member-Status', Auth::user()->derived_status_id);
				$response->header('PS-Member-Transactable', Auth::user()->derived_is_transactable);
			}
		}

        return $response;
	}
}