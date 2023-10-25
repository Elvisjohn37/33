<?php 
namespace Backend\middlewares;

use Closure;
use Layer;

class Guest {
	
	public function handle($request, Closure $next)
    {
    	Layer::service('Svalidate')->validate(array(
			'guest_access'  => array('value' => ''),
		), true);

        return $next($request);
    }
}