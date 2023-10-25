<?php

namespace Backend\models;

class Mhistorycommission extends Basemodel {
    
    protected $table      = 'historycommission';
    public    $timestamps = false;
    
    /**
     * Insert commission  default history
     * @param  array $data 
     * @return int
     */
    public function insert_history_commission($data)
    {
        return $this->Binsert($data);
    }
}
