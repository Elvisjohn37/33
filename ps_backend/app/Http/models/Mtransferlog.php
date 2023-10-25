<?php

namespace Backend\models;

class Mtransferlog extends Basemodel {

	protected $table      = 'transferlog';
	protected $primaryKey = 'transferLogID';
	protected $hidden     = array(
								'transferID'
							);
	public  $timestamps = false;

	/**
	 * This will insert to transferlog table and return transferLogID
	 * @param  array  $log_data transfer data
	 * @return int
	 */
	public function insert_transferlog($log_data)
	{
		return $this->Binsert($log_data);
	}
}
