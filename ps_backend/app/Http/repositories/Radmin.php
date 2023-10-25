<?php

namespace Backend\repositories;

use Exception;

/**
 * Radmin this repository is for all table that connected to admin 
 */
class Radmin extends Baserepository {


    public $models = array(
                        'Madmin'
                        
                    );

    /**
     * check if admin is login
     * @param  string  $sessionID 
     * @return boolean            
     */
    public function isLogin($sessionID)
    {

        $isLogin = $this->model('Madmin')->isLogin($sessionID);

        return $isLogin ? true : false;
    }
    
}