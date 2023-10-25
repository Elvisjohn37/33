<?php

namespace Backend\contracts;

Interface Slayerinterface {

	public function model($model_name);

	public function service($service_name);

	public function controller($controller_name);

	public function repository($repository_name);

}