<?php

namespace Backend\models;

class Mplatformconfig extends Basemodel {
    
    protected $table = 'platformconfig';
    
    /**
     * Get system time config in DB
     * @return object
     */
    public function get_system_time()
    {
        
        return $this->select('configValue')->where('configName','=','systemTime')->value('configValue');

    }
}
