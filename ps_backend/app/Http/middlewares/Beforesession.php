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

/**
*
* All route that is using a session(unde 'web' middleware) will pass here
*
* @author PS Team
*/
class Beforesession {

	
	public function handle($request, Closure $next)
    { 

		// User session validation
		if (Auth::check()) {

			$user       = Auth::user();

			try {
				Layer::service('Svalidate')->validate(array(
					'site_access'  => array('value' => $user)
				), TRUE);
				
				Layer::service('Svalidate')->validate(array(
					'login_access' => array('value' => $user),
					'block_request'=> array(
										'value'    => array(
														'request_url' => Request::route()->getPath(),
														'client'      => $user 
													),
									),
					'sessionID'    =>  array(
										'value'    => array(
														'db_sessionID'=> $user->sessionID,
														'sessionID'   => Layer::service('Ssession')->get('SESSION_ID')
													),
									),
					'lastActivity' =>  array(
										'value'    => array('lastActivity'=> $user->lastActivity)
									),
					'ip'           =>  array(
										'value'    => array('lastLoginIP'=> $user->lastLoginIP)
									)
				), true);

			} catch(Exception $e) {

				Layer::service('Ssession')->set_logout_sessions($user->clientID);

				// all response will be session timeout
				// specific responses should be handled by each modules
				// this is only a fail safe
				Layer::service('Ssocket')->push_main($user->sessionID,array('event' => 'timeout'));

				if (Request::ajax()) {
					return Response::json(json_decode($e->getMessage()));
				} else {
					return Redirect::to('/');
				}

			}

		}
		
        return $next($request);
    }
    
}