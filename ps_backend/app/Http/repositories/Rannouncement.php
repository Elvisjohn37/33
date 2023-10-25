<?php

namespace Backend\repositories;

use Auth;
use DateTime;
/**
* Repositories for all table connected to client data
*/
class Rannouncement extends Baserepository
{
	public $models = array(
						'Mannouncement'
					);

	/**
	 * get annoucment 
	 * @param  string $clientID   
	 * @param  string $whiteLabelID             
	 * @param  array  $announcement_config [display_days, date_format, language] 
	 * @return array                    
	 */
	public function get_announcements($clientID, $whiteLabelID, $announcement_config)
	{	

		$rows     = array();
		$language = $announcement_config['language'];

		$announcements = $this->model('Mannouncement')
							->get_announcements($clientID, $whiteLabelID, $announcement_config);

		foreach ($announcements as $key => $announcement) {

			$rows[] = array(
				'rowNumber' => ++$key,
				'date' 		=> custom_date_format($announcement_config['date_format'], $announcement->datePublished),
				'message'   => $announcement->content,
				'important' => $announcement->isImportant == 1 ? 'ps_important' :  ''
			);

		}
		
		return array('rows' => $rows, 'total' => count($rows), 'result' => true);
			
	}
}