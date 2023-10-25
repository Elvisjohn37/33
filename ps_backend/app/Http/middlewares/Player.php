<?php 
namespace Backend\middlewares;

use Closure;
use Layer;

class Player {

	public function handle($request, Closure $next)
    {
    	Layer::service('Svalidate')->validate(array(
			'is_login'  => array('value' => ''),
		), true);
		
        return $next($request);
    }
    
}