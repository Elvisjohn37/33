<?php

namespace Backend\services;

use Backend\contracts\Slayerinterface;
use Layer;

/**
 * Main objective of this base is to set rules for Services
 * 
 */
class Baseservice implements Slayerinterface {
	
	private $caller;

	public function __construct() 
	{
		$this->caller = get_class($this);
	}

	public function model($model_name) 
	{
		
		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $model_name

		));
	}

	public function service($service_name) 
	{

		return Layer::service($service_name);
		
	}	

	public function library($library_name) 
	{
		
		return Layer::library($library_name);
		
	}

	public function controller($controller_name) 
	{

		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $controller_name

		));
		
	}

	public function repository($repository_name) 
	{

		return Layer::repository($repository_name);
		
	}

}