<?php

namespace Backend\models;

class Mannouncement extends Basemodel
{
	protected $table  = 'announcement';
	protected $hidden = array('password', 'remember_token');

	/**
	 * get announcement for client and whitelabel
	 * @param  int $clientID          
	 * @param  string $whitelabelID      
	 * @param  int $announcement_days 
	 * @return array
	 */
	public function get_announcements($clientID, $whitelabelID, $announcement)
	{
		$valid_date = date('Y-m-d H:i:s', strtotime("-{$announcement['display_days']} days"));

		return $this->select('announcementcontent.content', 'announcement.datePublished', 'announcement.isImportant')
					->join('announcementcontent','announcementcontent.announcementID','=', 'announcement.announcementID')
					->join('clientann','announcement.announcementID', '=', 'clientann.announcementID')
					->where(function($query) use ($clientID,$whitelabelID) {

						$query->where('clientann.clientID','=',$clientID)
							->orWhere(array(
								array('clientann.clientID','=', 0),
								array('announcement.whiteLabelID','=',$whitelabelID)
							));

					})
					->where(array(
						array('clientann.clientTypeID','=',4),
						array('announcement.datePublished','>=',$valid_date)
					))
					->where('announcementcontent.languageCode', '=', $announcement['language'])
					->orderBy('announcement.isImportant','desc')
					->orderBy('announcement.datePublished','desc')
					->get();
			

	}
}