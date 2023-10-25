<?php

namespace Backend\exceptions;

use Exception;

class Slayerexception extends Exception {
	
    public function __construct($type, $args, $code = 0, Exception $previous = null) 
    {

    	switch ($type) {

    		case 'forbidden':

    			$message='Forbidden Layers Interaction: '.$args['caller'].' calling '.$args['called'].' '.$args['layer'];

    			break;

            case 'undefined_model':

                $message='Undefined model: '.$args['model'].' in '.$args['caller'];

                break;

            case 'missing_model':

                $message=$args['caller'].'::$models must be an array';

                break;

    		default:

    			$message="Unknown Error in Customclassinstanciator class";
    	}

        parent::__construct($message, $code, $previous);

    }

}