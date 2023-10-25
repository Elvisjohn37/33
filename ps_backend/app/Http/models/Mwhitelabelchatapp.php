<?php

namespace Backend\models;

class Mwhitelabelchatapp extends Basemodel {

	protected $table      = 'whitelabelchatapp';
	protected $primaryKey = 'whiteLabelChatAppID';

	/**
	 * This will get all chat operational for specific whiteLabelID
	 * @param  string $whiteLabelID whiteLabelID
	 * @return object        
	 */
	public function get_customer_supports($whiteLabelID)
	{

		return $this->select('status', 'application','content','whiteLabelChatAppID')
					->whitelabelID($whiteLabelID)
					->get();
        
	}

	/**
	 * get all caht app for whiteLabale that is online only
	 * @param  string $whiteLabelID 
	 * @return object               
	 */
	public function chat_by_whitelabelID($whiteLabelID)
	{
		return $this->online_fields()
					->whitelabelID($whiteLabelID)
					->get();
	}

	/**
	 * scopre that use whiteLabelID for where
	 * @param  object $query        
	 * @param  string $whiteLabelID 
	 * @return object               
	 */
	public function scopewhitelabelID($query, $whiteLabelID)
	{
		
		return $query->where('whiteLabelID','=', $whiteLabelID);

	}

	/**
	 * scope for select with same where
	 * @param  object $query 
	 * @return object        
	 */
	public function scopeonline_fields($query)
	{
		return $query->addselect('whiteLabelChatAppID','whiteLabelID','application')
					 ->where('status','=','online');
	}

	/**
	 * get all chat application of all whitelabels
	 * @return object 
	 */
	public function get_chat_apps()
	{
		return $this->online_fields()
					->get();
	}
}
