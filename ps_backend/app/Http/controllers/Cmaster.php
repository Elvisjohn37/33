<?php

namespace Backend\controllers;

use Input;

/**
 * Initialize landing pages and get view datas
 * 
 * @author PS Team
 */
class Cmaster extends Basecontroller {

	/**
	 * View initiator, create view payload
	 * @return object view instance
	 */
	public function view()
	{
		return $this->service('Sview')->create();
	}


	/**
	 * View initiator, create view payload
	 * @return object view instance
	 */
	public function view_data()
	{	
		return $this->service('Sview')->view_data(Input::get('payload'), Input::get('items'));
	}
}
