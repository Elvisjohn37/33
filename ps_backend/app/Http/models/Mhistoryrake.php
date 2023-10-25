<?php

namespace Backend\models;

class Mhistoryrake extends Basemodel {
    
    protected $table      = 'historyrake';
    public    $timestamps = false;
    
    /**
     * Insert rake history
     * @param  array $data 
     * @return int
     */
    public function insert_history_rake( $data )
    {
        return $this->Binsert($data);
    }
}
