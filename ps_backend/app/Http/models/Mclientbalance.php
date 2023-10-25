<?php

namespace Backend\models;

class Mclientbalance extends Basemodel 
{
    protected $table      = 'clientbalance';
    public    $timestamps = false;
    
    /**
     * add client balance 
     * 
     * @param type $clientID
     * @return type
     */
    public function insert_clientbalance($clientbalance)
    {
        return $this->Binsert($clientbalance);
    }

    /**
     * This will deduct player availableBalance only if it will not result to negative balance
     * @param  int    $clientID 
     * @param  int    $amount   
     * @return int             rows affected
     */
    public function deduct_availableBalance($clientID, $amount)
    {
        return $this->where('clientID', '=', $clientID)
                    ->where('availableBalance', '>=', $amount)
                    ->decrement('availableBalance',$amount);
    }

    /**
     * This will deduct poker available limit 
     * @param   int $clientID   
     * @param  int $amount      
     * @return  int            
     */
    public function deduct_pokerAvailableLimit($clientID, $amount)
    {
       return $this->where('clientID', '=', $clientID)
                    ->where('pokerAvailableLimit', '>=', $amount)
                    ->decrement('pokerAvailableLimit',$amount);
    }

    /**
     * get remaining pokeravailablelimit of client
     * @param   int $clientID  
     * @param  int $amount     
     * @return int             
     */
    public function remaining_pokerAvailableLimit($clientID, $amount)
    {
        return $this->select('pokerAvailableLimit')
                    ->where('clientID', '=', $clientID)
                    ->whereBetween('pokerAvailableLimit', [0,$amount])
                    ->value('pokerAvailableLimit');
    }
}
