<?php

namespace Backend\models;

class Mserverconfiguration extends Basemodel {

    protected $table      = 'serverconfiguration';
    public    $timestamps = false;
    
    /**
     * get url for serverID and configName
     * @param  string $serverID   
     * @param  string $configName 
     * @return mixed
     */
    public function get_config_url($serverID, $configName)
    {
        return $this->select('url')
            ->where('serverID','=',$serverID)
            ->where('configName','=',$configName)
            ->value('url');
    }
}
