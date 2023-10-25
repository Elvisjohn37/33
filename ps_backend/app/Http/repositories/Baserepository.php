<?php

namespace Backend\repositories;

use Backend\contracts\Slayerinterface;
use Layer;

/**
 * Main objective of this base is to set rules for Repositories
 * 
 */
class Baserepository implements Slayerinterface {
	
	private $caller;

	public function __construct() 
	{
		$this->caller = get_class($this);
	}

	public function model($model_name) 
	{
		if (isset($this->models) && is_array($this->models)) {

			if (in_array($model_name,$this->models)) {


				return Layer::model($model_name);


			} else {

				Layer::undefined_model(array(

					'model'  => $model_name,
					'caller' => $this->caller

				));

			}

		} else {

			Layer::missing_model(array(

				'caller' => $this->caller

			));

		}

	}

	public function service($service_name) 
	{

		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $service_name

		));
		
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
		
		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $repository_name

		));
		
	}
	
	public function library($library_name) 
	{
		
		return Layer::library($library_name);
		
	}

}