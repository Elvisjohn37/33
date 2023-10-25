<?php

namespace Backend\models;

class Mplayerregistration extends Basemodel {

    protected $table      = 'playerregistration';
    public    $timestamps = false;
    protected $primaryKey = 'clientID';
    protected $guarded    = array('clientID');
    
    /**
     * Insert player verification code
     * @param  array $player_registration 
     * @return int
     */
    public function insert_playerregistration($player_registration)
    {
        return $this->Binsert($player_registration);
    }


    /**
     * update new verification code of client
     * @param   int $clientID      
     * @param   array $update_data 
     * @return  int                
     */
    public function regenerate_verificationCode($clientID, $update_data)
    {      

        return $this->where('clientID','=', $clientID)
                    ->update($update_data);
        
    }

}
