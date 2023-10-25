<?php

namespace Backend\models;

class Mcommissiondefault extends Basemodel {
    
    protected $table      = 'commissiondefault';
    public    $timestamps = false;
    
    /**
     * This will insert commission default
     * @param  type $data
     * @return type
     */
    public function insert_commission_default($data)
    {

        return $this->Binsert($data);

    }

    /**
     * This will get child clientIDs parent commission default on a certain product
     * @param  int    $childClientID 
     * @param  int    $productID    
     * @return object
     */
    public function get_commission_default($childClientID, $productID)
    {
        return $this->select('clientID','forcedPT','minPT','takeRemaining','commissionRake')
                    ->where('childClientID', '=', $childClientID)
                    ->where('productID',     '=', $productID)
                    ->first();
    }
}
