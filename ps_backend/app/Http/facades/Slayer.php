<?php

namespace Backend\facades;

use  \Illuminate\Support\Facades\Facade;

class Slayer extends Facade {

    protected static function getFacadeAccessor() {  return 'Backend\services\Slayer'; }

}