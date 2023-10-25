<?php

namespace Backend\models;

class Madmin extends Basemodel {

    protected $table      = 'admin';
    public    $timestamps = false;
   

   /**
    * is admin login using sessinID
    * @param  string  $sessionID 
    * @return mixed            
    */
   public function isLogin($sessionID)
   {

        return $this->select('isLogin')
                    ->where('sessionID','=', $sessionID)
                    ->where('isLogin','=',1)
                    ->value('isLogin');
   } 
   
}
