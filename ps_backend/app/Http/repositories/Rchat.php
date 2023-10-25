<?php

namespace Backend\repositories;

use DateTime;  
use DateTimeZone;

/**
* Repository related to chat box
*/
class Rchat extends Baserepository
{
	public $models = array(
						'Mchat'
					);

	/**
	 * Get and format messages of client 
	 * @param  array  $users           [sender, receiver]
	 * @param  string $client_timezone 
	 * @param  array $message_config 
	 * @return array                 
	 */
	public function get_messages($user_info, $message_config, $last_chatID)
	{

		$dates = array('date_to' => date('Y-m-d'));

		$dates['date_from'] = custom_date_format('Y-m-d', previous_date(
				$dates['date_to'],
				$message_config['message_range']
		));

		$messages = $this->model('Mchat')->get_messages(
						$user_info,
						$dates, 
						array(
							'limit' => $message_config['limit_per_retrieve'],
							'last_chatID' => $last_chatID)
					);

		$return['has_message'] = false;
		$return['date']        = date('M d');

		if (count($messages) > 0) {

			$prev_date = date('Y-m-d');
			$prev_time = date('H:i');
			$return['last'] = 0;

			foreach ($messages as $message) {

				$return['has_message'] = true;

				$current_date 		  = custom_date_format('Y-m-d', $message->dateTime);
				$message->displayDate = timezone_format($message->dateTime, $user_info['client_timezone'], 'h:i A');
				$message->showDate 	  = false;
				$message->is_you 	  = ($user_info['sender'] == $message->sender);

				if (compare_dates($current_date, $prev_date, 'ne')) {
					    
				    $prev_date = $current_date;
				
					$prev_time = custom_date_format('H:i', $message->dateTime);
					$message->showDate = true;
					$message->displayDate = timezone_format(
												$message->dateTime, 
												$user_info['client_timezone'],
												'M d, Y h:i A'
											);

				} else {

					$current_time = custom_date_format('H:i', $message->dateTime);
					
					if((substract_dates($current_time,$prev_time,'minutes')) > $message_config['display_time']) {
						$prev_time = $current_time;
						$message->showDate = true;
					}

				}

				$return['msg'][] = $message;
				$return['last']  = $message->chatID;
			}

		}

		return $return;
		
	}

	/**
	 * Set other input for sending message
	 * @param  array $message [sender, receiver, message]
	 * @return datetime
	 */
	public function send_message($message)
	{	
		$replace   = array(
                        '>' => '&#62;',
                        '<' => '&#60;'
                    );

		$message['dateTime'] = date('Y-m-d H:i:s');
        $message['messages'] = str_replace(array_keys($replace), array_values($replace), $message['messages']);

		$this->model('Mchat')->insert_message($message);

		return array ('dateTime' => $message['dateTime'],
			'message'  => $message['messages']
		);
	}

	/**
	 * This will count unread chatbox from agent to player
	 * @param  string $sender   
	 * @param  string $receiver 
	 * @param  int    $message_range
	 * @return int
	 */
	public function count_unread($sender, $receiver, $message_range) 
	{	
		$dates = array('date_to' => date('Y-m-d'));

		$dates['date_from'] = custom_date_format('Y-m-d', previous_date(
				$dates['date_to'],
				$message_range
		));
		return $this->model('Mchat')->count_unread($sender, $receiver, $dates);

	}

	/**
	 * Mark as read all messages from sender to receiver
	 * @param  string $sender   
	 * @param  string $receiver 
	 * @return int           
	 */
	public function mark_as_read($sender, $receiver)
	{

		return $this->model('Mchat')->update_unread($sender, $receiver, array('isRead' => 1));

	}
}