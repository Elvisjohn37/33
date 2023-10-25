<?php

namespace Backend\models;

class Mclientsession extends Basemodel {

    protected $table      = 'clientsession';
    protected $fillable   = array('clientID');
    public    $timestamps = false;

    /**
     * Get session row of client's parent
     * @param  int $clientID 	
     * @return array
     */
    public function get_by_clientID($clientID) 
    {
        return $this->select('clientID', 'isLogin', 'isOnline', 'chatStatus')
                    ->clientID($clientID)
                    ->first();
    }

    /**
     * This will get sessionID of client if log in only
     * @param  int $clientID    
     * @return array
     */
    public function get_sessionID($clientID) 
    {
        return $this->select('sessionID')
                    ->clientID($clientID)
                    ->isLogin()
                    ->get();
    }
    
    /**
     * Add client session 
     * 
     * @param type $clientID
     * @return type
     */
    public function add_clientsession( $clientID )
    {
        return $this->Binsert( array(
                'clientID' => $clientID,
                'isOnline' => 1
            ));
    }

    /**
     * filter login only
     * @param  $query 
     * @return query
     */
    public function scopeisLogin($query)
    {

        return $query->where('isLogin','=',1);
        
    }

    /**
     * set client isOnline
     * @param int   $clientID 
     * @param array $update_fields value of isOnline
     * @return int
     */
    
    public function set_by_clientID($clientID, $update_fields)
    {
        return $this->clientID($clientID)->update($update_fields);
    }

    /**
     * Scope using clientID
     * @param  object $query    
     * @param  int $clientID 
     * @return eloquent object
     */
    public function scopeclientID($query, $clientID)
    {
        if (is_array($clientID)) {
            return $query->whereIN('clientID', $clientID);
        } else {
            return $query->where('clientID','=', $clientID);

        }

    }

    /**
     * This will count existing sessionID
     * @param  string $sessionID 
     * @return int
     */
    public function count_sessionID($sessionID)
    {
        return $this->where('sessionID','=',$sessionID)->count('sessionID');
    }

    /**
     * get clientIDs that inactive 
     * @param  string $last_timestamp 
     * @return array                 
     */
    public function get_inactive_clientIDs($last_timestamp)
    {

        return $this->clientIDs_isLogin()
            ->where('clientsession.lastActivity', '<=', $last_timestamp)
            ->where('clientsession.lastActivity', '!=', '0000-00-00 00:00:00')
            ->get();
        
    }

    /**
     * Scope for same select
     * @return array 
     */
    public function scopeclientIDs_isLogin($query)
    {
        return $query->select('client.clientID')
                    ->join('client', 'client.clientID','=', 'clientsession.clientID')
                    ->where('client.clientTypeID', '=', 4)
                    ->isLogin();
    }

    /**
     * get clientIDs by webtypename
     * @param  string $webTypeName 
     * @return array              
     */
    public function clientIDs_by_webTypeName($webTypeName)
    {
        return $this->clientIDs_isLogin()
                    ->leftJoin('webtype','webtype.webTypeID','=','clientsession.webTypeID')
                    ->where('webtype.webTypeName','=',$webTypeName)
                    ->get();
    }

    /**
     * update player lastActivity in DB
     * @param  int $clientID      
     * @param  array $update_fields fields to update
     * @return int              
     */
    public function update_lastActivity($clientID, $update_fields)
    {
        return $this->clientID($clientID)->update($update_fields);
    }

    public function count_marketingToken($marketingToken)
    {
        return $this->where('marketingToken','=',$marketingToken)->count('marketingToken');
    }

    /**
     * this will get all clientID that are not test player
     * @param  [type] $webTypeName [description]
     * @return [type]              [description]
     */
    public function clientIDs_not_TestPlayer($webTypeName)
    {
        return $this->clientIDs_isLogin()
                    ->leftJoin('webtype','webtype.webTypeID','=','clientsession.webTypeID')
                    ->where('client.isTestPlayer', '=', 0)
                    ->where('webtype.webTypeName','=',$webTypeName)
                    ->get();
    }
}
