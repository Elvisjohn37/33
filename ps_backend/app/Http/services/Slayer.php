<?php

namespace Backend\services;

use App;
use Backend\contracts\Slayerinterface;
use Backend\exceptions\Slayerexception;

/**
 * This is a special service, this will enable us to call Model and Controller layers via Facade
 * while keeping a single class instance, and having special rules
 *
 * FACADE Alias: Layer
 * @author PS Team
 */
class Slayer implements Slayerinterface {

	private $layer_namespaces = array(
								'controller' => 'Backend\controllers\\',
								'model'		 => 'Backend\models\\',
								'repository' => 'Backend\repositories\\',
								'service' 	 => 'Backend\services\\',
								'library'	 => 'Backend\libraries\\'
							);

	public $checker;

	private $rules = array();

	private $layer_instances = array();

	public function __construct()
	{
		
		// prepare layer instance container
		foreach ($this->layer_namespaces as $layer_method => $layer_namespace) {

			$this->layer_instances[$layer_method] = array();

		}
		
	}

	/**
	 * Accessing controller layers
	 * @return object
	 */
	public function controller($controller_name)
	{

		return $this->get_instance($controller_name,__FUNCTION__);

	}

	/**
	 * Accessing service layers
	 * @return object
	 */
	public function service($service_name)
	{

		return $this->get_instance($service_name,__FUNCTION__);

	}
	
	/**
	 * Accessing repository layers
	 * @return object
	 */
	public function repository($repository_name)
	{

		return $this->get_instance($repository_name,__FUNCTION__);

	}

	/**
	 * Accessing model layers
	 * @return object
	 */
	public function model($model_name)
	{

		return $this->get_instance($model_name,__FUNCTION__);

	}

	/**
	 * Accessing library layers
	 * @return object
	 */
	public function library($library_name)
	{

		return $this->get_instance($library_name,__FUNCTION__);

	}


	/**
	 * This will create class instance if not exists in current scope
	 * @param  string $class_name 
	 * @param  string $class_method 
	 * @return class instance
	 */
	public function get_instance( $layer_name, $layer_method )
	{
		$class_full_path = $this->layer_namespaces[$layer_method].$layer_name;
		
		if (!array_key_exists( $layer_name, $this->layer_instances[$layer_method] )) {

			$this->layer_instances[$layer_method][$layer_name]=App::make($class_full_path);

		}

		return $this->layer_instances[$layer_method][$layer_name];

	}

	/**
	 * Throw fobidden layer interaction error
	 */
	public function forbidden($args) 
	{

		throw new Slayerexception('forbidden', $args);

	}

	/**
	 * Throw undefined model error
	 */
	public function undefined_model($args) 
	{

		throw new Slayerexception('undefined_model', $args);

	}

	/**
	 * Throw missing models list error
	 */
	public function missing_model($args) 
	{

		throw new Slayerexception('missing_model', $args);

	}
}