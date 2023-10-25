<?php

namespace Backend\models;

class Mprofilelog extends Basemodel {

	protected $table      = 'profilelog';
	protected $primaryKey = 'transferLogID';
	public    $timestamps = false;

	/**
	 * This will insert to transferlog table and return transferLogID
	 * @param  array  $log_data transfer data
	 * @return int
	 */
	public function insert_profilelog($log_data)
	{
		return $this->Binsert($log_data);
	}
}
