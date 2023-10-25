<?php

    /*
	|--------------------------------------------------------------------------
	| RSO paths (old)
	|--------------------------------------------------------------------------
	|
	*/
	$config['resource_path']     = 'https://rso.338alab.com';
	$config['rso_absolute_path'] = 'https://rso.338alab.com';
	$config['rso_absolute_path'] = 'https://rso.338alab.com';

    /*
	|--------------------------------------------------------------------------
	| SPORTS LOBBY
	|--------------------------------------------------------------------------
	|
	*/
	$config['SPORTS_LOBBY_URL']='https://sportsbook.DOMAIN.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';

    /*
	|--------------------------------------------------------------------------
	| WL SETTINGS
	|--------------------------------------------------------------------------
	|
	*/
	$config['WL_CODE']        = '';
	$config['WL_IS_ORIGINAL'] = 1;

    /*
	|--------------------------------------------------------------------------
	| Email Users
	|--------------------------------------------------------------------------
	|
	*/
	$config['EMAIL_SENDER']   = 'support@338a.com';
	$config['EMAIL_RECEIVER'] = 'lp_dev@leekie.com';


    /*
	|--------------------------------------------------------------------------
	| REGISTRATION
	|--------------------------------------------------------------------------
	|
	*/
	$config['BONUS_NEW_MEMBER'] = 0;
	$config['REGISTRATION_PARENTS'] = array(15);

    /*
	|--------------------------------------------------------------------------
	| DB QUERY LOG
	|--------------------------------------------------------------------------
	|
	*/
	$config['ENABLE_QUERY_LOG'] = true;

    /*
	|--------------------------------------------------------------------------
	| DB QUERY LOG
	|--------------------------------------------------------------------------
	|
	*/
	$config['ENABLE_QUERY_LOG'] = true;

    /*
	|--------------------------------------------------------------------------
	| ANN_RESX(old)
	|--------------------------------------------------------------------------
	| Savvy announcement resources
	|
	*/
	$config['ANN_RESX'] =	'https://ars.DOMAIN.com';

    /*
	|--------------------------------------------------------------------------
	| savvy
	|--------------------------------------------------------------------------
	|
	| Savvy assets and plugin settings
	|
	*/
	$config['savvy'] = array(
						'announcement' => array(
											'css' => 'https://ars.338alab.com/css/jquery.marquee.css',
											'js'  => 'https://ars.338alab.com/scripts/savvy_announcement.js'
										) 
					);


    /*
    |--------------------------------------------------------------------------
    | promotion
    |--------------------------------------------------------------------------
    |
    | All promotion system settings
    */
	$config['promotion']['new_member_enabled'] = false;

    /*
	|--------------------------------------------------------------------------
	| RSO
	|-------------------------------------------------------------------------
	*/
	$config['rso']['all']['assets']                  = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['all']['wl_assets']               = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['fallback']['wl_assets_frontend'] = 'https://www.DOMAIN.com/assets/';