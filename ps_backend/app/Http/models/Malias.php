<?php

namespace Backend\models;

class Malias extends Basemodel  {

	protected $table = 'alias';

	/**
	 * Get sessionID of parent alias
	 * @param  int  $clientID 
	 * @return array            
	 */
	public function get_sessionID($clientID)
	{

		return $this->select('sessionID')->aliases($clientID)->isLogin()->pluck('sessionID');

	}

	/**
	 * count online aliases of parent
	 * @param  int $clientID
	 * @return int          
	 */
	public function count_online($clientID)
	{

		return $this->aliases($clientID)->isLogin()->count();

	}

	/**
	 * Scope query session of clientID
	 * @param  $query 
	 * @param  int $clientID 
	 * @return query
	 */
	public function scopeAliases($query, $clientID)
	{

		return $query->where('clientID', '=', $clientID);

	}

	/**
	 * Get session who's login
	 * @param  $query 
	 * @return query
	 */
	public function scopeisLogin($query)
	{

		return $query->where('isLogin','=',1);
		
	}
}