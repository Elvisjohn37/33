<?php 
namespace Backend\middlewares;

use Input;
use Closure;
use Layer;
use Response;

class Csrf {

	public function handle($request, Closure $next)
    {
		$return   = Layer::service('Ssession')->validate_csrf(Input::get("_token"));

		if (isset($return['error'])) {

			return response($return['error'], 200)
			->header("PS-Token",$return['token'])
			->header('Content-Type', 'application/json');

		}


        $response = $next($request);

        $response->header("PS-Token",$return['token']);
		
		return $response;
    }

}