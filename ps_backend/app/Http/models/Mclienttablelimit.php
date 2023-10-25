<?php

namespace Backend\models;

class Mclienttablelimit extends Basemodel {

	protected $table 	  = 'clienttablelimit';
	public    $timestamps = false;
	Protected $fillable   = array('clientID', 'tableLimitID');

	/**
	 * get tablelimitID of parent by clientID
	 * @param  int $clientID id of parentID
	 * @return object           eloquent object of clienttblelimit;
	 */
	public function get_by_clientID($clientID)
	{
		
		return $this->select('tableLimitID')->where('clientID', '=', $clientID)->get();
		
	}

	/**
	 * inherit the client's parent tablelimit
	 * @param  array $clienttablelimit value insert for clienttablelimit
	 * @return none         
	 */
	public function insert_clienttablelimit($clienttablelimit)  
	{

		return $this->Binsert($clienttablelimit);

	}
}
