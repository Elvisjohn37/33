<?php

namespace Backend\middlewares;

use Layer;
use Closure;

class Setstartsession {
	
	public function handle($request, Closure $next)
    {
		Layer::service('Ssiteconfig')->set_start_session();
        return $next($request);
    }

}