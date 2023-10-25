<?php

namespace Backend\models;

class Mbetsetting extends Basemodel {

	protected $table 	  = 'betsetting';
	protected $primaryKey = 'betSettingID';
	Protected $fillable   = array('clientID', 'gameID');
	public    $timestamps = false;

	/**
	 * getSetting get parent besetting
	 * @param  int $clientID is the parentID of client from client table
	 * @return object           eloquent object of betsetting 
	 */
	public function get_by_clientID($clientID) 
	{
		
		return $this->select("betSettingID", "clientID", "gameID")
					->where("clientID","=", $clientID)
					->get();

	}
	
	/**
	 * inherit register betsetting of parent to client
	 * @param  array $betsetting value to insert in betsetting table
	 * @return int             betsettingID of client
	 */
	public function insert_betsetting($betsetting)  
	{
		return $this->Binsert($betsetting);
	}
}
