<?php

namespace Backend\repositories;


/**
* Repository related to guest data
*/
class Rguest extends Baserepository
{
	public $models = array(
						'Mguest'
					);

	/**
	 * Get information of guest
	 * @param  string $sessionID 
	 * @return mixed           
	 */
	public function guest_information($sessionID)
	{
		return $this->model('Mguest')->get_name($sessionID);

	}

	/**
	 * This will generate new guest sessionID only
	 * @param  int    $max_try
	 * @return string
	 */
	public function generate_sessionID($max_try)
	{
        $try = 0;

        while ($try <= $max_try) {

            $sesssionID      = $this->unverified_sessionID();
            $count_sessionID = $this->model('Mguest')->count_sessionID($sesssionID);

            if ($count_sessionID <= 0) {

                return $sesssionID;

            }

            $try++;

        }

        return false;
	}

	/**
	 * This will generate new guest name only
	 * @param  int    $max_try 
	 * @return string
	 */
	public function generate_name($max_try)
	{
		$try = 0;

        while ($try <= $max_try) {

            $name       = $this->unverified_name();
            $count_name = $this->model('Mguest')->count_name($name);

            if ($count_name <= 0) {

                return $name;

            }

            $try++;
        }

        return false;
	}

	/**
	 * This will generate non verified(not yet checked if existing in DB) guest name
	 * @return string
	 */
	private function unverified_name()
	{
		return 'GUEST'.random_number(8);
	}

	/**
	 * This will generate non verified(not yet checked if existing in DB) sessionID
	 * @return string
	 */
	private function unverified_sessionID()
	{
		return str_random(32);
	}

	/**
	 * This will generate a while new record of guests and will be inserted to guest table
	 * @param  int 	 $parentID
	 * @param  int   $max_try 
	 * @return array
	 */
	public function create($parentID, $max_try)
	{
		$try = 0;

        while ($try <= $max_try) {

            $guest_data = array(
            				'name' 		=> $this->unverified_name(),
            				'sessionID' => $this->unverified_sessionID(),
            				'parentID'	=> $parentID,
            				'ipAddress' => get_ip()
            			);

            $guest      = $this->model('Mguest')->create_not_exist($guest_data);
            
            if (!$guest->exists) {

                return $guest_data;

            }

            $try++;
        }

        return false;
	}

	/**
	 * Get guestID using guestname
	 * @param  string $guest_name 
	 * @return int             
	 */
	public function get_guestID($guest_name)
	{
		return $this->model('Mguest')->get_guestID($guest_name);
	}
}