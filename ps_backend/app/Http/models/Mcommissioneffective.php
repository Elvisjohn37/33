<?php

namespace Backend\models;

class Mcommissioneffective extends Basemodel {
    
    protected $table      = 'commissioneffective';
    public    $timestamps = false;
    
    /**
     * Inert commsission effective
     * @param  array $data 
     * @return int
     */
    public function insert_commission_effective ( $data )
    {
        return $this->Binsert($data);
    }

}
