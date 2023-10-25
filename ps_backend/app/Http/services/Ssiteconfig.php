<?php

namespace Backend\services;

use Config;
use Auth;
use Request;
use URL;
use Lang;
use DateTime;
use DateTimeZone;
use App;
use Exception;
use Cookie;

/**
 * This will help us transform some data in config file into something meaningful
 * This will may also contain methods meant to configure some part of website
 * @author PS Team
 */
class Ssiteconfig extends Baseservice {

	private $theme_config_keys = array(
									'common' => 'theme_common',
									'player' => 'theme_player',
									'guest'  => 'theme_home'
								);

	private $saved_data = array();

	/**
	 * This will check first previous data being fetched and return it if already have
	 * @param  string    $key      
	 * @param  function  $callback
	 * @return mixed
	 */
	private function get_saved_data($key, $callback)
	{
		if (isset($this->saved_data[$key])) {

			return $this->saved_data[$key];
			
		} else {

			return $this->saved_data[$key] = $callback();

		}

	}

	/**
	 * This will get the theme config key t be used
	 * @param  string  $item_key  
	 * @param  boolean $is_common
	 * @return string
	 */
	private function get_theme_key($item_key, $is_common=false) 
	{

		if ($is_common) {

			return $this->theme_config_keys['common'].'.'.$item_key;

		} else {

			if (Auth::check()) {
				
				return $this->theme_config_keys['player'].'.'.$item_key;

			} else {

				return  $this->theme_config_keys['guest'].'.'.$item_key;

			}

		}

	}

	/**
	 * Inherit Config::get method
	 * @param  string $key     
	 * @param  mixed  $default 
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return Config::get($key, $default);
	}

	/**
	 * This will get default location of our site
	 * @param  string $default_location if not null then we will use this as default
	 * @return string
	 */
	public function get_default_location($default_location = false)
	{
		if ($default_location) {
			
			return $default_location;

		} else {

			$theme_default_location = $this->theme('default_location');

			if ($theme_default_location) {
				
				return $theme_default_location;

			} else {

				return '#home';

			}

		}
	}

	/**
	 * This will get the item on theme_common, theme_home, theme_player
	 * This save all previously fetched items incase our program needs it again
	 * @param  String $item_key
	 * @return mixed
	 */
	public function theme($item_key) 
	{	

		return $this->get_saved_data('theme'.$item_key, function() use($item_key) {	

			// get in theme_home, theme_player if key exists
			$custom_config_key = $this->get_theme_key($item_key);
			
			if (Config::has($custom_config_key)) {

				return Config::get($custom_config_key);

			} else {

				$common_config_key =  $this->get_theme_key($item_key, true);
				
				if (Config::has($common_config_key)) {

					return Config::get($common_config_key);

				} else {

					return null;

				}

			}

		});
	}

	/**
	 * This will get menu and submenu combined in one array
	 * @param  boolean 		 $single_array_list If set to true this will return submenu and menu as single array only
	 *                                          else this will return array with menu and submenu as different items
	 * @param  array/boolean $include_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											only included in the menu
	 * @param  array/boolean $exclude_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											excluded in the menu, this will be filtered last, so if productID
	 * 											exists in included_menu it will be overwrited by this       	
	 * @return array
	 */
	public function get_all_menu($single_array_list = true, $include_menu = false, $exclude_menu = false)
	{
		$suffix = '';

		if (is_array($include_menu) && count($include_menu) > 0) {

			$suffix = 'in'.implode('',$include_menu);

		}

		
		if (is_array($exclude_menu) && count($exclude_menu) > 0) {

			$suffix .= 'ex'.implode('',$exclude_menu);

		}

		$menu_list = $this->get_saved_data('all_menu'.$suffix, function() use($include_menu, $exclude_menu) {

						$grouped_menu = $this->theme('menu');
						$has_included = (is_array($include_menu) && count($include_menu) > 0);
						$has_excluded = (is_array($exclude_menu) && count($exclude_menu) > 0);
						
						if ($has_included || $has_excluded) {

							foreach ($grouped_menu as &$menu) {

								if (is_array($include_menu)) {
									$menu = array_only($menu, $include_menu);
								}

								if (is_array($exclude_menu)) {
									$menu = array_except($menu, $exclude_menu);
								}
							}

						}

						return $grouped_menu;
					});

		if ($single_array_list) {
			
			return $this->get_saved_data('single_all_menu', function() use ($menu_list) {

				$ungrouped_menu = array();

				foreach ($menu_list as $menu) {
					
					assoc_array_merge($ungrouped_menu,$menu);

				}

				return $ungrouped_menu;

			});

		} else {

			return $menu_list;

		}
	}

	/**
	 * This will get the prerequisite page of the  user
	 * @param  object $user 
	 * @return string
	 */
	public function get_prerequisite_page($client = false)
	{

		if (Auth::check()) {

			if ($client === false) {

				$client = Auth::user();

			}

			// check if user needs to change credentials
			if ($client->isFirstLogin && !$client->isWalkIn) {

				return 'change_credentials';

			}
			
			// check if user needs to accept terms and condition
			if (!$client->isTermsAccepted) {

				return 'accept_terms';

			}

			// check if user needs to change its password
			$is_reset = $this->service('Ssession')->get('checkPassReset');
			if ($this->is_password_expired($client->lastChangePassword) || $is_reset) {

				return 'expired_password';

			}
		} 
	}

	/**
	 * This will check if date given surpassed the expired password config
	 * @param  string   $lastChangePassword
	 * @return boolean                
	 */
	public function is_password_expired($lastChangePassword) 
	{
		$lastChangePassword_elapse = substract_dates($lastChangePassword, date('Y-m-d H:i:s'), 'months');

		return ($lastChangePassword_elapse >= Config::get('settings.user.password_expiration_months'));
	}

	/**
	 * This will get menu items that are not disabled by clientproduct or PS config
	 * @param  boolean		 $single_array_list If set to true this will return submenu and menu as single array only
	 *                                      	else this will return array with menu and submenu as different items
	 * @param  array/boolean $include_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											only included in the menu
	 * @param  array/boolean $exclude_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											excluded in the menu, this will be filtered last, so if productID
	 * 											exists in included_menu it will be overwrited by this       
	 * @return array
	 */
	public function get_enabled_menu($single_array_list = true, $include_menu = false, $exclude_menu = false)
	{

		$suffix = '';

		if (is_array($include_menu) && count($include_menu) > 0) {

			$suffix = 'in'.implode('',$include_menu);

		}

		
		if (is_array($exclude_menu) && count($exclude_menu) > 0) {

			$suffix .= 'ex'.implode('',$exclude_menu);

		}

		$menu_list = $this->get_saved_data('enabled_menu'.$suffix, function() use($include_menu, $exclude_menu) {

						$menu_list 			 = $this->get_all_menu(false, $include_menu, $exclude_menu);
						$disabled_productIDs = $this->disabled_menu_productIDs();
						$disabled_submenu    = array();

						foreach ($menu_list as $menu_type => &$menu_items) {
							foreach($disabled_productIDs as $index => $productID) {
								if (isset($menu_items[$productID]['submenu'])) {
									array_push($disabled_submenu,$menu_items[$productID]['submenu']);
								} 
							}
							
							array_remove_keys($menu_items,$disabled_productIDs);

						}
							array_remove_keys($menu_list,$disabled_submenu);

						return $menu_list;

					});

		if ($single_array_list) {
			
			return $this->get_saved_data('single_enabled_menu', function() use ($menu_list) {

				$ungrouped_menu = array();

				foreach ($menu_list as $menu) {
					
					assoc_array_merge($ungrouped_menu, $menu);

				}

				return $ungrouped_menu;

			});

		} else {

			return $menu_list;

		}
	}

	/**
	 * This will get all disabled productIDs list for menu
	 * @return 
	 */
	public function disabled_menu_productIDs()
	{
		return $this->get_saved_data('disabled_productIDs', function() {

			$disabled_productIDs = Config::get('settings.MENU_DISABLED');

			if (Auth::check()) {

				$disabled_clientproduct = $this->repository('Rproducts')
											->get_missing_productIDs(Auth::user()->productID);

				seq_array_merge($disabled_productIDs,$disabled_clientproduct);

			}

			return $disabled_productIDs;

		});
	}

	/**
	 * This will detect if site is stil new base on config
	 * @return boolean 
	 */
	public function is_new() 
	{

		if (Config::has('settings.launch')) {

			if (strtotime(date('Y-m-d')) <= strtotime(Config::has('settings.launch.end'))) {

				return true;

			}

		}

		return false;

	}
	
	/**
	 * Get sites currencyID to be used
	 * @return string
	 */
	public function currencyID()
	{

		// get the app base currencyID if user is not logged in
		if (Auth::check()) {

			return Auth::user()->currencyID;

        } else {

			return Config::get("settings.currency.base_currencyID");

        }

	}

	/**
	 * This will get rso URL of a given type and its fallback
	 * @param  string  $path_config     config key, (e.g. assets, wl_assets, common_assets)
	 *                                  The middle part of Path/URL from config file
	 * @param  string  $url_for         config key, (e.g. frontend, backend, self_hosted)
	 *                                  This will be the first part of URL from config file
	 * @param  string  $path_extension  This will be concated at the last part of URL/path
	 * @return string 
	 */
	public function rso($path_config = 'assets', $url_for = 'frontend', $path_extension = '')
	{
		// get the path
		$get_path = function($type) use($path_config, $url_for, $path_extension) {

			switch ($type) {

				case 'original': 

					$path_key_specific = 'settings.rso.'.$url_for.'.'.$path_config;
					$path_key_all 	   = 'settings.rso.all.'.$path_config;

					if (Config::has($path_key_specific)) {
						
						$path_key = $path_key_specific; 

					} else {

						$path_key = $path_key_all; 

					}

					$base_url = (Config::get('settings.rso.'.$url_for.'_url')  == '') ? asset('/assets') : Config::get('settings.rso.'.$url_for.'_url');
					
					break;

				case 'fallback': 

					$path_key = 'settings.rso.'.$type.'.'.$path_config.'_'.$url_for; 
					$base_url = ''; // fallbacks are absolute and complete url already, no need to assign something

					break;
			}

			if (Config::has($path_key)) {
				
				return $this->get_dynamic_domain($base_url.Config::get($path_key).$path_extension);

			} else {

				return false; 

			}

		};
		
		$original = $get_path('original');
		$fallback = $get_path('fallback');

		return compact('original','fallback');
	}

	/**
	 * This will get all rso URL by type
	 * @param  string $url_for default = frontend
	 * @return array
	 */
	public function rso_all($url_for = 'frontend')
	{
		return $this->get_saved_data('rso_all'.$url_for, function() use($url_for) {	

			$paths_configs 			= array_keys(Config::get('settings.rso.all'));
			$paths_configs_specific = array_keys(Config::get('settings.rso.'.$url_for));
			seq_array_merge($paths_configs,$paths_configs_specific);

			$final_paths   = array('original' => array(),'fallback' => array());

			foreach ($paths_configs as $path_config) {
				$get_url							   = $this->rso($path_config, $url_for);
				$final_paths['original'][$path_config] = $get_url['original'];
				$final_paths['fallback'][$path_config] = $get_url['fallback'];
			}

			return $final_paths;

		});
	}

	/**
	 * This will get rso_folder of the site
	 * @param  boolean $is_rso truthy(default) = this will get the folder name, falsy = this will be set as 0 
	 * @return type
	 */
	public function rso_folder()
	{
		if (Config::has("settings.rso.game_rso_folder")) {


			if (Cookie::get('ps_is_rso')) {
				
				return Config::get("settings.rso.game_rso_folder");

			} else {

				return 0;

			}

		} else {

			return 0;

		}
	}

	/**
	 * Get url any assets in self_hosted(asset_sys) directory
	 * @param  string  $path_extension    
	 * @param  string  $public_url
	 * @param  boolean $path_config 
	 * @return string                
	 */
	public function self_hosted_asset($path_extension = '', $is_public = false, $path_config = 'assets')
	{
		// self hosted has no fallback
		$url = $this->rso($path_config, 'self_hosted' , $path_extension)['original'];

		if ($is_public) {
			
			return asset('/').$url;

		} else {

			return public_path().'/'.$url;

		}
	}

	/**
	 * This will get js or css source path, depends if the system need minified or not
	 * @param  string $type 			css, js
	 * @param  string $path_extension 
	 * @return string
	 */
	public function inline_script($type, $path_extension = '')
	{	
		$path_extension = $this->script_path($type).$path_extension;
		return $this->self_hosted_asset($path_extension);
	}

	/**
	 * This will get URL only for 
	 * @param  string $type 			css, js
	 * @param  string $path_extension 
	 * @param  string $path_config      default
	 * @return mixed
	 */
	public function frontend_script($type, $path_extension = '', $path_config = 'assets')
	{	
		$script_path = $this->script_path($type);

		return $this->rso($path_config, 'frontend', $script_path.$path_extension);

	}

	/**
	 * This will get js or css source path inside rso, depends if the system needs minified or not
	 * @param  string $type 			css, js - if empty this will get all as array
	 * @return string/array
	 */
	public function script_path($type = '')
	{	
		$minified_suffix = '_minified';

		$getter = function($type) use($minified_suffix) {

					if (Config::get('settings.rso.compress')) {
						
						return Config::get('settings.rso.script_paths.'.$type.$minified_suffix);

					} else {

						return Config::get('settings.rso.script_paths.'.$type);

					}

				};

		if ($type == '') {

			$script_paths_keys = array_keys(Config::get('settings.rso.script_paths'));
			$script_paths 	   = array();

			foreach ($script_paths_keys as $key) {
				
				if (!str_contains_last($key, $minified_suffix)) {

					$script_paths[$key] = $getter($key);

				}

			}

			return $script_paths;

		} else {

			return $getter($type);

		}
	}

	/**
	 * This will get all possible info of rso
	 * @param  string $dot_notation 
	 * @return mixed
	 */
	public function rso_full($dot_notation = null) 
	{

		$rso_full_info = array(
			                'compress'     => $this->get('settings.rso.compress'),
			                'urls'         => $this->rso_all(),
			                'self_hosted'  => $this->self_hosted_asset('', true),
			                'script_paths' => $this->script_path()
		        		);

		if (!empty($dot_notation)) {

			return array_get($rso_full_info, $dot_notation);

		} else {

			return $rso_full_info;

		}

	}
        
    /**
     * check if platform is in mobile
     * 
     * @return boolean
     */
    public function is_mobile_platform()
    {
        if ( Config::has("settings.IS_MOBILE_PLATFORM") ){

            return Config::get("settings.IS_MOBILE_PLATFORM");
        }
        
        return false;
    }

   /**
     * check if platform is mobile ready
     * 
     * @return boolean
     */
    public function is_mobile_ready()
    {
        if (Config::has("settings.MOBILE_READY")) {

            return Config::get("settings.MOBILE_READY");
        }
        
        return true;
    }

    /**
     * This will get dynamic domain 
     * @param  string $url 	
     * @return string
     */
	public function get_dynamic_domain($url)
	{

		if (Config::get('settings.DYNAMIC_URL') && strpos($url, 'DOMAIN.') !== false) {

	        $host = remove_subdomain($this->get_url());

	        $parsed_url			    = custom_parse_url($url);
   			$host_removed_subdomain = remove_subdomain($parsed_url['host']);

	        return str_replace($host_removed_subdomain,$host,$url);

	    } else {

	        return $url;

	    }

	}

	/**
	 * This will get current site URL
	 * @return string
	 */
	public function get_url()
	{
		if (Config::has('settings.default_url')) {

            return Config::get('settings.default_url');

        } else {

            return URL::to('/');

        }
	}

	/**
	 * This will get current site URL
	 * @return string
	 */
	public function get_site_domains()
	{		

		if (Config::has('settings.site.subdomains')) {
			
			$subdomains = Config::get('settings.site.subdomains');

		} else {

			$subdomains = array('desktop' => 'www', 'mobile' => 'm');

		}

        $site_url 	  = $this->get_url();
		$site_domains = array('host' => remove_subdomain($this->get_url()));

		foreach ($subdomains as $key => $subdomain) {
			
			$site_domains[$key] = replace_subdomain($site_url, $subdomain);

		}

		return $site_domains; 

	}

	/**
	 * This will return accouting time
	 * @return array
	 */
	public function accounting_time()
	{
		$accounting_time = Config::get('settings.transactions.accounting_time');

		if (is_date_format($accounting_time,'H:i:s') == false) {

			$accounting_time = '08:00:00';

		}

		return $accounting_time;
	}

	/**
	 * This will get the offset and limit to be used 
	 * depending on given page and config
	 * @param  int    $page       
	 * @param  string $paging_for 
	 * @return array
	 */
	public function paging_offset_limit($page, $paging_for = 'report')
	{
		if (!is_numeric($page) || $page<=0) {

			$page = 1;

		}

		// get limit
		$limit_config_key = 'settings.'.$paging_for.'.row_per_page';

		if (Config::has($limit_config_key)) {
			
			$limit = Config::get($limit_config_key);

		} else {

			$limit = 20;

		}
		
		// get offset
		if (empty($limit)) {
			$offset = null;
		} else {
			$offset = ($page - 1) * $limit;
		}

		return compact('limit', 'offset');

	}

	/**
	 * this will check if fund transfer allows withdrawal
	 * @return boolean
	 */
	public function ft_allow_withdrawal()
	{

        if (Config::has('settings.transactions.fund_transfer.allow_withdrawal')) {
            
            return Config::get('settings.transactions.fund_transfer.allow_withdrawal');

        }

        return false;

	}

	/**
	 * This will determine if a word is foul base on our configuration
	 * @param  string  $word 
	 * @return boolean     
	 */
	public function is_fould_word($word)
	{

        $word = strtolower($word);

		$foul_words = lang::get('foul_words');

        foreach ($foul_words as $foul_word) {

            $foul_word = strtolower($foul_word);

            if (strpos($word,$foul_word) !== false) {

                return true;

            }

        }

        return false;
	}

	/**
	 * Get test agents clientID list, test players under this agents can login to any WL
	 * @return array     
	 */
	public function test_agents_whitelist()
	{
  		if (Config::has('settings.AGENT_WHITELIST')) {
            
            return Config::get('settings.AGENT_WHITELIST');

        } else {

        	return false;

        }
	}

	/**
	 * This will get all productIDs websession config
	 * This has default hardcoded config because this is a must
	 * @return array
	 */
	public function websession_products()
	{
		return $this->get_saved_data(__FUNCTION__, function() {

			if (Config::has('settings.websession.products')) {
				
				return Config::get('settings.websession.products');

			} else {

				return array(

					'delete' => array(

									'force_delete'  => array(5,6,4),
									'check_coinin'  => array(2,3)
									
								),

					'create' => array(

									'force_create'  => false,	
									'ps_managed'	=> array(5,6),
									'per_gameType'	=> array(2,3)

								)
				);
			}

		});
	}

	/**
	 * This will get the url get param &lang format for specific productID base on site current language
	 * This is usally being used for third party products
	 * @param  array $game [gameID,serverID,productID]
	 * @return string
	 */
	public function game_lang_format($game)
	{
		$language_id = $this->get_lang_id(); 

        // determine game langID format via serverID (first attempt for now)
		switch ($game['serverID']) {

			case 'GPI':

				if (Config::has('settings.games.GPI.language.'.$language_id)) {
					
					return Config::get('settings.games.GPI.language.'.$language_id);

				} else {

					return $language_id;

				}

				break;
		}

        // determine game langID format via productID (last attempt)
		switch ($game['productID']) {
			case 5:
			case 6:
			case 8:

				if ($language_id != 'en') {
					
					return $language_id .= '-'.$language_id;

				}

				break;
		}


		return $language_id;
	}

	/**
	 *  This will tell if player should use native chatbox or livechatinc plugin
	 * @return boolean 
	 */
	public function is_native_chatbox()
	{
		return $this->theme('chat')['type'] === 'native';
	}

	/**
	 * This will get current site agent
	 * @param   int    $currencyID
	 * @return  array
	 */
	public function get_site_agent($currencyID = false)
	{
		if ($currencyID === false) {
			
			$currencyID = Config::get('settings.currency.base_currencyID');

		}

		return $this->repository('Rplayer')->get_assigned_agent(
			Config::get('settings.WL_CODE'),
			$currencyID,
			Config::get('settings.REGISTRATION_PARENTS')
		);
	}

	/**
	 * This will get this site current mode
	 * @return boolean
	 */
	public function get_app_mode()
	{	
		$app_mode = $this->repository('Rdbconfig')->get_app_mode(Config::get('settings.WEBTYPE_NAME'));

		// if guest we need to check if site already has agent
		// no need to check if already logged in(This means they already have parentID)
		if ($app_mode['app_mode'] == true && !Auth::check()) {
			
			// if has parentID session then this site is good
			// if no parentID session, ask repo if there's really no assigned parent to make sure
			if (!$this->service('Ssession')->get('parentID') && !$this->get_site_agent()) {
				
				$app_mode['app_mode'] = false;

			}

		}

		return $app_mode;
	}

	/**
	 * This will get all fund transferable wallets
	 * @return array
	 */
	public function transferable_walletIDs()
	{
		if (Config::has('settings.products.transferable_walletIDs')) {
			
			return Config::get('settings.products.transferable_walletIDs');

		} else {

			return array(6);

		}
	}

	/**
	 * This will get all fund transferable wallets
	 * @param  array $productIDs List of productIDs that uses a walletID, 
	 *                           If not array this will get the Auth::user()-productID
	 * @return array
	 */
	public function get_wallets($productIDs = false)
	{
		if (!is_array($productIDs)) {
			
			$productIDs = Auth::user()->productID;

		}

		return $this->get_saved_data('wallets'.implode('.',$productIDs), function() use($productIDs) {	

			$wallets = array(
						Config::get('settings.products.house_walletID') => Config::get('settings.PRODUCT_NAME')
					);
			
			return assoc_array_merge(
				$wallets,
				$this->repository('Rproducts')->get_product_wallets(
					$this->transferable_walletIDs(),
					$productIDs,
					array($this->service('Scrypt'),'crypt_encrypt')
				)
			);

		});
	}
	
	/**
	 * This will get page sidebars and filter it by its own method
	 * Each page sidebars may have a set of closures that can add elements or remove sidebar 
	 * This closures can be set using sidebar methods: create sidebar_<pagename> 
	 * Then this method can return: 'array' which will be added to sidebar attributes,
	 * 								'false' which will remove the sidebar on the list
	 * @param string 	 $menu_id             
	 */
	public function get_sidebar($menu_id)
	{
		$sidebars = $this->theme('sidebars.'.$menu_id);

		if ($sidebars) {

			foreach ($sidebars as $key => &$sidebar) {

				$closure = $this->sidebar_closures($sidebar['id']);

				// additional filter/data for sidebar
				if (is_callable($closure)) {

					$closure_return = $closure($sidebar);

					if ($closure_return === false) {
						
						unset($sidebars[$key]);

					} else {

						if (is_array($closure_return)) {
							
							assoc_array_merge($sidebar, $closure_return);

						}
					}
				}
			}
			
			return array_values($sidebars);
		}
	}

	/**
	 * Set closures that will remove or add element to menuID sidebars
	 * @param  int/string $menuID
	 * @return array                closure arrays
	 */
	private function sidebar_closures($sidebar_id) 
	{
		$closures = array(
			/*
		     |----------------------------------------------------------------------------------------------------------
		     | Help
		     |----------------------------------------------------------------------------------------------------------
			 */
			'faq' => function($sidebar) {

				// children
				$faq_productIDs = Config::get('settings.help.faq_productIDs');
				$children       = isset($sidebar['children']) ? $sidebar['children'] : array();
				return array(
					'children' => $this->repository('Rproducts')
													->product_as_menu(
														$faq_productIDs,
														$children,
														function($productName, $productID) {
															return  array(
																'id'        => $this->service('Ssiteconfig')
																	->productName_formatter($productName),
                                								'productID' => $this->service('Scrypt')
                                    								->crypt_encrypt($productID)
                                    							);
														}
													)
				);

			},

			'gaming_rules' => function($sidebar) {

				// children
				$gm_productIDs = Config::get('settings.help.gaming_rules_productIDs');
				$children      = isset($sidebar['children']) ? $sidebar['children'] : array();
				return array(
					'children' => $this->repository('Rproducts')
													->product_as_menu(
														$gm_productIDs,
														$children,
														function($productName, $productID) {
															return  array(
																'id'        => $this->service('Ssiteconfig')
																	->productName_formatter($productName),
                                								'productID' => $this->service('Scrypt')
                                    								->crypt_encrypt($productID)
                                    							);
														}
													)
				);

			},

			'game_guide' => function($sidebar) {
				$children = isset($sidebar['children']) ? $sidebar['children'] : array();

				// children
				return array('children' => $this->service('Scache')->game_guide_menu($children));
			},

			/*
		     |----------------------------------------------------------------------------------------------------------
		     | Account
		     |----------------------------------------------------------------------------------------------------------
			 */
			'fund_transfer' => function() {

				try {

					$this->service('Svalidate')->validate(array(

						'fund_access' => array('value' => Auth::user())

					), true);

				} catch (Exception $e) {

					return false;

				}

			},

			'withdrawal_request' => function() {

				try {
					
					$this->service('Svalidate')->validate(array(

						'withdrawal_access' => array('value' => Auth::user())

					), true);

				} catch (Exception $e) {
					
					return false;

				}

			}
		);

		if (isset($closures[$sidebar_id])) {
			
			return $closures[$sidebar_id];

		} else {

			return null;

		}
	}

	/**
	 * This will get current chat status 
	 * @param  string $parentID 
	 * @return array
	 */
	public function chatStatus($parentID) 
	{
        $chatStatus     = $this->repository('Rplayer')->chatStatus_by_clientID($parentID);
        $is_walkin_type = (!Auth::check() || Auth::check() && Auth::user()->isWalkIn);

        if ($is_walkin_type && $chatStatus['status']=='offline' || $chatStatus['chatStatus']=='hide') {
        	$chatStatus['can_send'] = false;
        } else {
        	$chatStatus['can_send'] = true;
        }

        return $chatStatus;
	}

	/**	
	 * This will return all WS topics to be subscribed
	 * @param  array $payload 
	 * @return array
	 */
	public function websocket_topics($payload)
	{	
		$wl_code = strtolower(Config::get('settings.WL_CODE'));
		$rooms   = array(
					Config::get('settings.websocket.global_topic'),
					(Config::get('settings.WEBTYPE_NAME') == 'Player Site') ? 
							Config::get('settings.WEBTYPE_NAME') : 
							strtolower(Config::get('settings.WEBTYPE_NAME'))
				);

		if (!Auth::check()) {
			array_push($rooms, $wl_code.'_guest');
		}

		if (is_array($payload) && isset($payload['route'])) {

			switch ($payload['route']) {

				case 'game_window':
					array_push($rooms, 'ps_game_window');
					break;
				
				default:
					array_push($rooms, 'ps_main_window');
					break;
			}
			
		}

		if (Config::get('settings.WL_CODE') == '') {
			$rooms[] = 'original';
		} else {
			$rooms[] = 'white label';
			$rooms[] = $wl_code;
		}
		return $rooms;
	}

	/**
	 * Get theme lang as array with additional 'is_active' field
	 * @return 
	 */
	public function theme_lang_array()
	{
		$languages  = $this->theme('languages');
		$lang_array = array();

		foreach ($languages as $langID => $text) {
			array_push($lang_array, array(
				'langID'    => $langID,
				'text'      => $text,
				'is_active' => ($langID == $this->get_lang_id())
			));
		}

		return $lang_array;
	}

	/**
	 * This will get bank dropdown of a given whiteLabelID
	 * @param  string $whiteLabelID If no whiteLabelID given this will get from config
	 * @return array
	 */
	public function bank_dropdown($whiteLabelID=null)
	{
		if ($whiteLabelID === null) {
			$whiteLabelID = Config::get('settings.WL_CODE');
		}

		return $this->repository('Rwhitelabel')->get_bank_dropdown($whiteLabelID);
	}

	/**
	 * This will get currency dropdown of a given whiteLabelID
	 * @return array
	 */
	public function currency_dropdown()
	{	
		
		$currency_config = array(
			'whiteLabelID'         => Config::get('settings.WL_CODE'),
			'registration_parents' => Config::get('settings.REGISTRATION_PARENTS')
		);	

		return $this->repository('Rwhitelabel')->currency_dropdown($currency_config);
		
	}

	/**
	 * This will check if new member bonus is enabled
	 * @return boolean
	 */
	public function new_bonus_enabled() 
	{
		$promotionID = Config::get('settings.promotion.new_member_promotionID');
		$config_file = Config::get('settings.promotion.new_member_enabled');
		return $config_file && $this->repository('Rdbconfig')->is_promotion_enabled($promotionID);
	}

	/**
	 * This will check if new member bonus is enabled
	 * @return boolean
	 */
	public function new_bonus_dropdown() 
	{
		$dropdown_config = Config::get('settings.promotion.new_member_dropdown');
		$dropdown_length = count($dropdown_config);
		$dropdown_array  = array();

		for ($i=0;$i<$dropdown_length;$i++) {
			$dropdown_value = $dropdown_config[$i];
			$dropdown_array[$dropdown_value] =  ($dropdown_value * 100).'%';
		}

		return $dropdown_array;
	}

	/**
	 * This get all securityQustions
	 * @return array
	 */
	public function securityQuestions() 
	{
		return array(
            Lang::get('language.security_question1'),
            Lang::get('language.security_question2'),
            Lang::get('language.security_question3'),
            Lang::get('language.security_question4'),
            Lang::get('language.security_question5'),
            Lang::get('language.security_question6'),
            Lang::get('language.security_question7')
        );
	}

	/**
	 * This will get statement dates according to config
	 * @return void
	 */
	public function statement_dates() 
	{
		$statement_months = Config::get('settings.report.statement_months');
		$statement_dates  = array();
		for ($i=$statement_months; $i >= 1; $i--) {
			$date             = previous_date(date('Y-m-01'), $i-1, 'months');
			$year             = custom_date_format('Y', $date);
			$month_first_date = custom_date_format('M 01', $date);
			$month_last_day   = month_last_day($date);

			array_push($statement_dates, array(
				'number'     => $i,
				'date_range' => $month_first_date.' - '.$month_last_day.' '.$year,
				'date'       => $date,
				'month'      => custom_date_format('m', $date),
				'year'       => $year,
			));
		}

		return $statement_dates;
	}

	/**
	 * This will get all savvy announcement assets in config file
	 * @return array
	 */
	public function savvy_announcement_assets() 
	{
		$config = Config::get('settings.savvy.announcement');

		foreach ($config as $key => &$url) {
			$url = $this->get_dynamic_domain($url);
		}

		return $config;
	}

	/**
	 * get system time from DB
	 * @return string 
	 */
	public function get_system_time()
	{
		return $this->repository('Rdbconfig')->get_system_time();
	}

	/**
	 * Get lang id from cookie else session else get default
	 * @return string
	 */
	public function get_lang_id()
	{
		$lang_id = 'id';
		if (Cookie::get('ps_lang_id') !== null ) {

			$lang_id = Cookie::get('ps_lang_id');

		} else if (Config::has("settings.DEFAULT_LANGUAGE")) {
			
			$lang_id = Config::get("settings.DEFAULT_LANGUAGE");

		}

		return $lang_id;
	}

	/**
	 * Set lang id
	 * @param string $lang_id 
	 * @return string
	 */
	public function set_lang_id($lang_id)
	{	
		Cookie::queue('ps_lang_id', $lang_id);
		App::setLocale($lang_id);
		return $lang_id;
	}

	/**
	 * Get lang id from cookie else session else get default
	 * @return string
	 */
	public function init_lang_id()
	{	
		$lang_id = $this->get_lang_id();
		$this->set_lang_id($lang_id);
		return $lang_id;
	}

	/**
	 * Compute previous time 
	 * @return string 
	 */
	public function last_timestamp()
	{
		return previous_date(date('Y-m-d H:i:s'), Config::get('session.inactive'), 'minutes');
	}

	/**
	 * This will set true if session start
	 * 
	 */
	public function set_start_session()
	{	
		Config::set('start_session',true);

	}

	/**
	 * Get if session start
	 * @return bool 
	 */
	public function get_start_session()
	{

		return is_null(Config::get('start_session')) ? false : Config::get('start_session');
	}

	/**
	 * Get  gameID's disbled
	 * @return array 
	 */
	public function serverIDs_disabled()
	{
		
		return Config::has('settings.serverIDs_disabled') ?  Config::get('settings.serverIDs_disabled') : array();
	}

	/**
	 * This will append unique key for the game wiht the name
	 * @param  	int $gameID   
	 * @param  	string $gameName 
	 * @return  string           
	 */
	public function gameName_unique_key($gameID, $gameName, $productName = null)
	{
		
		$new_gameName = alphanum_remove_space($gameName);
		$unique_key   = Config::get("settings.game.unique_key.{$gameID}");
		$unique_key   = is_null($unique_key) ? $unique_key : "_{$unique_key}"; 
		$gameName_key = is_null($productName) ? "{$new_gameName}{$unique_key}" : alphanum_remove_space($productName)."_{$new_gameName}{$unique_key}";

		return $gameName_key;
	}

	/**
	 * this will format the product name
	 * @param  string $productName 
	 * @return string              
	 */
	public function productName_formatter($productName)
	{

		return alphanum_remove_space($productName);
	}

	/**	
	 * This will get css file to be use for specific route
	 * @param  string $route
	 * @return string
	 */
	public function view_css($route) 
	{
		if (Config::has('settings.rso.view_css.'.$route)) {
			return array(
				'main' => Config::get('settings.rso.view_css.'.$route)
			);
		} else {
			return array(
				'main' => Config::get('settings.rso.view_css.default')
			);
		}
	}

	/**
	 * This will get current platform
	 * @return string
	 */
	public function platform() 
	{
		return $this->is_mobile_platform() ? 'mobile' : 'desktop';
	}

	/**
	 * Get the list of disabled gameIDs, productIDs, serverIDs in game guide
	 * @return array
	 */
	public function disabled_game_guide() 
	{
		return array(
	    	'disabled_gameIDs'    => Config::get('settings.help.disabled_game_guides'),
	    	'disabled_serverIDs'  => array_unique(array_merge(
										$this->serverIDs_disabled(),
										Config::get('settings.help.serverID_without_GG')
									)),
	    	'disabled_productIDs' => $this->disabled_menu_productIDs()
	    );
	}

	/**
	 * This will detect if game has game guide
	 * @param  array   $game [gameID,serverID,productID]
	 * @return boolean  
	 */
	public function has_game_guide($game) 
	{
		$disabled_IDs = $this->disabled_game_guide();

		if (isset($game['productID']) && in_array($game['productID'], $disabled_IDs['disabled_productIDs'])) {
			return false;
		}

		if (isset($game['serverID']) && in_array($game['serverID'], $disabled_IDs['disabled_serverIDs'])) {
			return false;
		}

		if (isset($game['gameID']) && in_array($game['gameID'], $disabled_IDs['disabled_gameIDs'])) {
			return false;
		}

		return true;
	}
}