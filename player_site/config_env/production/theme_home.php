<?php
	
	$config['menu']['primary'] = array(
						0 => array(
		                	'text'     =>'',
		                	'page'     =>'banner',
		                	'id'       =>'home'
						),
						3 => array(
		                	'text'     =>'{{@lang.language.tangkas}}',
		                	'page'     =>'banner',
		                	'id'       =>'tangkas',
		                    'featured' => array('soon' => false)
						),
						1 => array(
		                	'text'     =>'{{@lang.language.games}}',
		                	'page'     =>'banner',
		                	'id'       =>'games',
		                    'featured' => array('soon' => false)
						),
						7 => array(
		                	'text'     =>'{{@lang.language.multiplayer}}',
		                	'page'     =>'banner',
		                	'id'       =>'multiplayer',
		                    'featured' => array('soon' => false)
						),
						101 => array(
		                	'text'      =>'{{@lang.language.promotions}}',
		                	'page'      =>'promo',
		                	'id'        =>'promo',
		                    'has_badge' => true,
		                    'featured'  => array('soon' => false)
						),
						102 => array(
		                	'text'     =>'{{@lang.language.tournament}}',
		                	'page'     =>'tournament',
		                	'id'       =>'tournament'
						),
						106 => array(
		                	'text'          =>'{{@lang.language.help}}',
		                	'page'          =>'help',
		                	'id'            =>'help',
		                    'floating'      => true,
		                    'first_sidebar' => false
						)
					);
    /*
    |--------------------------------------------------------------------------
    | chat
    |--------------------------------------------------------------------------
    |
    | chatbox settings
    |
    */
	$config['chat']['type'] = 'livechatinc';