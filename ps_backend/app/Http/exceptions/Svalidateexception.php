<?php

namespace Backend\exceptions;

use Exception;

class Svalidateexception extends Exception {
	
    public function __construct($validate_return, $code = 0, Exception $previous = null) 
    {
        parent::__construct(json_encode($validate_return), $code, $previous);

    }

}