<?php

namespace Backend\models;

class Mclientpromotion extends Basemodel {
    
    protected $table      = 'clientpromotion';
    public    $timestamps = false;
    
    /**
     * This will insert new promotion for client
     * @param  array $data
     * @return int
     */
    public function insert_client_promotion($data)
    {
        return $this->Binsert($data);
    }
}
