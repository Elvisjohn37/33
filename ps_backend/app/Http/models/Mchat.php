<?php

namespace Backend\models;

class Mchat extends Basemodel {

	protected $table      = 'chat';
	protected $hidden     = array('chatID');
	public    $timestamps = false;

	/**
	 * Get message of sender 
	 * @param  array  $users  [sender, receiver]
	 * @param  array  $dates  date_from, date_to
	 * @return object         
	 */
	public function get_messages($users, $dates, $chat_info)
	{

		$query = $this->select('chatID','sender','receiver','messages','dateTime')
					->where(function($query) use ($users) {
						$query->where(function($query) use ($users) {
							return $query->where('sender', '=', $users['sender'])
										->where('receiver', '=', $users['receiver']);
						})->orWhere(function($query) use ($users) {
							return $query->where('sender', '=', $users['receiver'])
										->where('receiver', '=', $users['sender']);
						});
					})
					->message_date($dates)
					->orderBy('chatID', 'DESC')
					->limit($chat_info['limit']);

		if (is_null($chat_info['last_chatID'])) {
			return $query->get();
		} else {

			return $query->where('chatID','<', $chat_info['last_chatID'])
			->get();
		}

	}

	/**
	 * Mark isRead true 	
	 * @param string $sender 	
	 * @param string $update_fields 	
	 */
	public function update_unread($sender, $receiver, $update_fields)
	{

		return $this->unread($sender, $receiver)->update($update_fields);
	}

	/**
	 * Count unread messages 	
	 * @param string $sender 	
	 * @param string $receiver 	
	 * @param array  $dates 	
	 * @param array  $isRead   
	 */
	public function count_unread($sender, $receiver, $dates)
	{
		return $this->unread($sender, $receiver)
					->message_date($dates)
					->count('chatID');
	}

	/**
	 * Filter message by dates
	 * @param  object $query 
	 * @param  array $dates 
	 * @return int        
	 */
	public function scopemessage_date($query, $dates)
	{
		return $query->whereDate('dateTime', '<=', $dates['date_to'])
					->whereDate('dateTime', '>=', $dates['date_from']);
	}
	/**
	 * Filter unread messages
	 * @param  object $query    
	 * @param  string $sender   
	 * @param  string $receiver
	 * @return object
	 */
	public function scopeUnread($query, $sender, $receiver)
	{
		return $query->where('sender','=', $sender)
					->where('receiver','=', $receiver)
					->where('isRead','=', 0);
	}

	/**
	 * insert new message
	 * @param  $array $message 
	 * @return int          
	 */
	public function insert_message($message)
	{

		return $this->Binsert($message);

	}
	
}
