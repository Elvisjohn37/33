<?php

namespace Backend\models;

class Mguest extends Basemodel  {

	protected $table      = 'guest';
	protected $primaryKey = 'guestID';
	protected $hidden     = array('guestID');
	protected $guarded    = array('guestID');
	public    $timestamps = false;
	

	/**
	 * Get temporary name of guest 
	 * @param  string $sessionID 
	 * @return mixed
	 */
	public function get_name($sessionID)
	{
		return $this->select('name')
					->where('sessionID','=', $sessionID)
					->value('name');
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
     * This will count existing name
     * @param  string $name 
     * @return int
     */
    public function count_name($name)
    {
        return $this->where('name','=',$name)->count('name');
    }

    /**
     * This will create guest record if the sessionID and name does not exists yet
     * @param  array $guest_data 
     * @return int
     */
    public function create_not_exist($guest_data)
    {
        return $this->Bcontains_or_create(
            array_only($guest_data, array('name','sessionID')), 
            array_except($guest_data, array('name','sessionID'))
        );
    }

    /**
     * get guestID
     * @param  string $guest_name 
     * @return int             
     */
    public function get_guestID($guest_name)
    {
        return $this->select('guestID')
                    ->where('name','=', $guest_name)
                    ->value('guestID');
    }
}
