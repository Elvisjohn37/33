<?php

namespace Backend\models;

class Mwebtype extends Basemodel {

    protected $table      = 'webtype';
    public    $timestamps = false;
    
    /**
     * Get application settings from DB
     * 
     * @param type $webTypeName
     * @return type
     */
    public function get_app_mode($webTypeName) 
    {
        
        return $this->select('mode')
                    ->webTypeName($webTypeName)
                    ->first();
    }

    /**
     * This will get webTypeID by given webTypeName
     * 
     * @param type $webTypeName
     * @return type
     */
    public function get_webTypeID($webTypeName) 
    {
        return $this->webTypeName($webTypeName)->value('webtypeID');
    }

    /**
     * This will filter query by webTypeName
     * @param  object $query       
     * @param  string $webTypeName 
     * @return object
     */
    public function scopewebTypeName($query, $webTypeName)
    {
        return $query->where('webTypeName', '=', $webTypeName);
    }
}
