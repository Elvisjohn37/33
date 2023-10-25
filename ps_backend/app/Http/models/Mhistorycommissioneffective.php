<?php

namespace Backend\models;

class Mhistorycommissioneffective extends Basemodel {
    
    protected $table      = 'historycommissioneffective';
    public    $timestamps = false;

    /**
     * Insert commission effective history
     * @param  array $data 
     * @return int
     */
    public function insert_history_commEffective($data)
    {
        return $this->Binsert($data);
    }
}
