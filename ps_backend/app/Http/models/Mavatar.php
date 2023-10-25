<?php

namespace Backend\models;

class Mavatar extends Basemodel {

	protected $table      = 'avatar';
	public    $timestamps = false;
	protected $primaryKey = 'avatarID';
	protected $fillable   = ['clientID', 'imgOrder', 'filename', 'status', 'isActive'];

	/**
	 * count avatar of client
	 * @param  int $clientID 
	 * @return int           
	 */
	public function count_avatars($clientID)
	{

		return $this->clientID($clientID)->count('clientID');
	}

	/**
	 * scope for clientID or with imgOrder
	 * @param  object  $query         
	 * @param  int  $clientID      
	 * @param  boolean $with_imgOrder 
	 * @return object                 
	 */
	public function scopeclientID_orwith_imgOrder($query, $clientID, $with_imgOrder = false)
	{

		return $query->where(function($where) use($clientID, $with_imgOrder) {
			$where->where('clientID','=', $clientID);

			if ($with_imgOrder) {
				$where->where('imgOrder', '=',$with_imgOrder);
			}

		});

	}

	/**
	 * insert client avatar 
	 * @param  array $avatar 
	 * @return int         
	 */
	public function insert_avatar($avatar)
	{

		return $this->Binsert($avatar);
	
	}

	/**
	 * get information of client's avatar
	 * @param  int $clientID 
	 * @return object           
	 */
	public function get_info($clientID)
	{
		return $this->select('filename', 'imgOrder', 'status', 'isActive')
			->clientID_orwith_imgOrder($clientID)
			->orderBy('imgOrder', 'DESC')
			->get();
	}

	/**
	 * This will return avatar if client's avatar is available for uploading
	 * @param  int $clientID 
	 * @param  int $imgOrder 
	 * @return array           	
	 */
	public function check_available_upload($clientID, $imgOrder)
	{

		return $this->clientID_orwith_imgOrder($clientID ,$imgOrder)
			->where(function($query) {
				$query->where('isActive', '=', 0)
					->orWhere(array(
						array('isActive', '=', 1),
						array('status', '=', 0),
					));
			})
			->first();

	}

	/**
	 * Update the client avatar with uploaded image
	 * @param int $clientID 
	 * @param int $imgOrder 
	 * @param array $fields  
	 * @return  int  
	 */
	public function set_avatar($clientID,$imgOrder,$fields)
	{

		return $this->clientID_orwith_imgOrder($clientID ,$imgOrder)
			->update($fields);

	}

	/**
	 * check if avatar is available to set as avatar
	 * @param  int $clientID 
	 * @param  int $imgOrder 
	 * @return mix null or array           
	 */
	public function check_set_primary($clientID, $imgOrder)
	{
		return $this->clientID_orwith_imgOrder($clientID ,$imgOrder)
			->where('isActive', '=', 0)
			->first();

	}

	/**
	 * reset active avatar
	 * @param  int $clientID 
	 * @param  int $imgOrder 
	 * @return int           
	 */
	public function reset_active($clientID,$imgOrder)
	{
		return $this->where('clientID','=', $clientID)
			->where('imgOrder','!=', $imgOrder)
			->update(array('isActive' => 0));
	}

	/**
	 * set avatar as profile avatar
	 * @param int $clientID 
	 * @param int $imgOrder 
	 * @return  int 
	 */
	public function set_primary($clientID, $imgOrder)
	{
		return $this->clientID_orwith_imgOrder($clientID ,$imgOrder)
			->whereIn('status', [0,1])
			->update(array('isActive' => 1));

	}

}