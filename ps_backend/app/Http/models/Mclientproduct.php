<?php

namespace Backend\models;

class Mclientproduct extends Basemodel {

    protected $table      = 'clientproduct';
    public    $timestamps = false;
    protected $fillable   = array('clientID');
    
    /**
     * Data to check if the product is applicable for the player
     * 
     * @param int/mix $clientID
     * @return object
     */
    public function get_applicable_products($clientID) 
    {
        
        return $this->select('productID')->where('clientID', '=', $clientID)->first();

    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public function insert_client_product($data)
    {
        return $this->Binsert($data);
    }

}
