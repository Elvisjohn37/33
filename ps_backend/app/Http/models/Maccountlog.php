<?php

namespace Backend\models;

class Maccountlog extends Basemodel {

	protected $table      = 'accountlog';
	protected $primaryKey = 'accountLogID';
	public    $timestamps = false;

	/**
	 * This will insert to transferlog table and return transferLogID
	 * @param  array  $log_data transfer data
	 * @return int
	 */
	public function insert_accountlog($log_data)
	{
		return $this->Binsert($log_data);
	}
}
