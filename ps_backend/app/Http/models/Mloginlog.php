<?php

namespace Backend\models;

class Mloginlog extends Basemodel {

	protected $table      = 'loginlog';
	protected $primaryKey = 'loginLogID';
	public    $timestamps = false;

	/**
	 * This will insert to loginlog table and return loginLogID
	 * @param  array  $log_data 
	 * @return int
	 */
	public function insert_loginlog($log_data)
	{
		return $this->Binsert($log_data);
	}


	/**
	 * This will insert to loginlog table and return loginLogID
	 * @param  array  $sessionIDs list of sessionIDs
	 * @param  array  $log_data   
	 * @return int
	 */
	public function update_loginlog($sessionIDs, $log_data)
	{
		return $this->whereIn('sessionID',$sessionIDs)->update($log_data);
	}
}
