<?php

namespace Backend\services;

use Cache;
use Exception;
use Config;
use Auth;

/**
 * This will help us get and set items in our cache
 * 
 * @author PS Team
 */
class Scache extends Baseservice {

	private $alias = array(
						'bsi_plugin' => 'plugin'
					);

	/**
	 * This will add prefix to the declared cache key/index
	 * This will also get the real key if its being aliased
	 * @param  string $key cache key/index
	 * @return string      final cache key
	 */
	private function key_wrapper($key) 
	{

		// get real key if alias only
		if (array_key_exists($key , $this->alias)) {

			$key=$this->alias[$key];

		}

		return "private_ps_". Config::get("settings.WL_CODE") ."_".$key;

	}

	/**
	 * is_cache_available will check cache avialability when performing cache operations
	 * @param  function $callback   callback function if cache is available
	 * @param  mixed 	$fallback   fallback return value if cache operation fails, default = false
	 * @return mixed 				
	 */
	private function is_cache_available($callback, $fallback = false)
	{
        try {

            return $callback();

        } catch (Exception $error) {

        	// log error
        	$this->service('Slogger')->file(

        		array(
	        		'error' 	 => $error->getmessage(),
	        		'connection' => Config::get('database.redis.default')
        		),

        		'CACHE_ERROR'

        	);

            return $fallback;

        }

	}


	/**
	 * Similar to Cache::get but with added cache availability checking
	 * @param  string $key 
	 * @return array    
	 */
	public function get($key)
	{

		return $this->is_cache_available(

			// callback function
			function() use ($key) {

				$wrapped_key = $this->key_wrapper($key);

		        $cache_value = array();

		        if (Cache::has($wrapped_key)) {

		            $cache_value = custom_json_decode(Cache::get($wrapped_key), true);

		        }

		        return $cache_value;
			},

			// fallback value
			array()

		);

	}

	/**
	 * Similar to Cache::put but with added cache availability checking
	 * @param  string $key   					
	 * @param  array  $value 					
	 * @param  int    $expiration_time_minutes
	 * @return boolean
	 */
	public function put($key, $value, $expiration_time_minutes = 5) 
	{

		return $this->is_cache_available(

			// callback function
			function() use ($key, $value, $expiration_time_minutes) {

				// only array with value can be set to cache
		        if (is_array($value) && count($value) > 0) {

		            $wrapped_key = $this->key_wrapper($key);

		            Cache::put($wrapped_key, json_encode($value), $expiration_time_minutes);

		            return true;

		        } else {

		        	return false;
		        }

			}

		);
    }

    /**
     * Get all cacheable plugin data, auto refresh if no data yet
     * we have bsi and asi plugin cache
     * @param  mixed $put_data if this optional parameter has value it means we're setting plugin value
     * @return mixed
     */
    public function get_plugin()
    {

		if (Auth::check()) {

			$key='asi_plugin';

	    } else {

			$key='bsi_plugin';

	    }
	    
	    $plugin_data=$this->get($key);
		
		// refresh cache if empty
		if (count($plugin_data) <= 0) {

			$cached_plugins = $this->service('Ssiteconfig')->theme('plugin_cache')['enable'];

			// get from DB
	        foreach ($cached_plugins as $plugin_name) {

	            $plugin_data[$plugin_name] = $this->refresh_plugin($plugin_name);

	        }

	        $this->put($key,$plugin_data);

		}

		return $plugin_data;

    }

    /**
     * This will get refresh plugin data from DB and get it ready for caching 
     * @param  $plugin_name
     * @return mixed
     */
    public function refresh_plugin($plugin_name)
    {
        switch (remove_space($plugin_name)) {
        	
        	case 'bankoperational':

        		$plugin_data = $this->repository('Rwhitelabel')->get_bank_supports(
			        			Config::get("settings.WL_CODE"),
			        			Auth::check()
			        		);

        		break;

        	case 'chatoperational':

        		$plugin_data = $this->repository('Rwhitelabel')->get_customer_supports(Config::get("settings.WL_CODE"));

        		break;

        	case 'sidebanner':

        		$plugin_data = $this->repository('Rwhitelabel')->get_side_banners(
			        			Config::get("settings.WL_CODE"),
			        			Config::get('settings.plugin.sidebanner.limit')
			        		);

        		break; 

        	case 'banner':

        		$plugin_data = $this->repository('Rwhitelabel')->get_banners(
        						array(
        							'whiteLabelID' 		  => Config::get("settings.WL_CODE"),
									'limit_per_productID' => Config::get('settings.plugin.banner.limit_per_productID')
        						),
			        			$this->service('Ssiteconfig')->get_all_menu(),
			        			array($this->service('Scrypt'),'crypt_encrypt')
			        		);

        		break;

        	case 'badge' : 

    			$plugin_data    = array();
        		$menu_list      = $this->service('Ssiteconfig')->get_all_menu();

				foreach ($menu_list	 as $productID => $menu) {

					if (isset($menu['has_badge']) && $menu['has_badge'] === true) {

						if (str_contains($productID , '.') == false) {

							// initiate getter if needed only
							if (!isset($get_badge)) {

        						$get_badge = function($productID) {

												switch ($productID) {

													case 101:

														return $this->repository('Rwhitelabel')
																	->count_promotions(Config::get("settings.WL_CODE"));

													default : 

														return $this->repository('Rproducts')
																   	->count_new_games(
																   		$productID,
																   		$this->service('Ssiteconfig')
																	   		->serverIDs_disabled()
																   	); 

												}
												
				        					};

							}

							$plugin_data[$menu['id']] = $get_badge($productID);

						}
					}

				}

        		break;

        	case 'lastresult' : 

		        $lastresult = NULL;
		        
		        try {
		        	
			        $data = $this->service('Ssiteconfig')->get_url();
			        $togel_histories = $this->service('Sapi')->request('togel_lobby_information', array(
			            'url' => $data
			        ))['histories'];

			        foreach ($togel_histories as $histories) {

			        	if ($histories['drawResult']) {
			        		if (is_null($lastresult)) {

								$lastresult = str_split($histories['drawResult']);
			        		}
					    }

			        }

		        } catch (Exception $e) {
		        	$lastresult = array('-','-','-','-');
		        }

        		$plugin_data[] =  array(
        			'gameName'   => 'Togel',
        			'lastResult' => $lastresult
	        	);

        		break;

            case 'jackpot': 

                // with callback
                $plugin_data = $this->repository('Rproducts')->get_jackpots(Config::get('settings.plugin.disabled_productIDs.jackpots'));

                break;

            case 'news'   : 

                $plugin_data = $this->repository('Rwhitelabel')->get_news(
			                    Config::get('settings.plugin.news'),
			                    Config::get('settings.WL_CODE'),
			                    $this->service('Ssiteconfig')->get_lang_id()
			                );

                break;
            
            case 'transactions':

                $plugin_data = $this->repository('Rtransactions')->get_plugin_data(

			                    Config::get('settings.plugin.transactions'),

			                    // site_information
			                    array(
			                        'is_check_qouta'  => $this->service('Ssiteconfig')->is_new(),
			                        'start_date'      => Config::get('settings.launch.start_date')
			                        					.' '
			                        					.$this->service('Ssiteconfig')->accounting_time(),
			                        'whiteLabelID'    => Config::get('settings.WL_CODE')
			                    )

			                );

                break;
        }

        // check if result can be json_encoded else return empty
        if (is_json_encodable($plugin_data) == false) {
        	
        	$plugin_data = array();

        }

        return $plugin_data;

    }

    /**	
     * This will get game guide hierarchy
	 * product 
	 *   - game type  
	 *       - game
	 * @param  array  $existing_menu  If gameName and existing id is equal the existing detials will be merged
     *                                Items that has no same gameNames will be prepended to the root
     * @return array
     */
    public function game_guide_menu($existing_menu)
    {
    	$disabled_IDs = $this->service('Ssiteconfig')->disabled_game_guide();
    	sort($disabled_IDs['disabled_gameIDs']);
    	sort($disabled_IDs['disabled_productIDs']);
    	sort($disabled_IDs['disabled_serverIDs']);
		$key_suffix   = implode('',$disabled_IDs['disabled_gameIDs'])
						.implode('',$disabled_IDs['disabled_productIDs'])
						.implode('',$disabled_IDs['disabled_serverIDs']);

		$cache_key    = 'game_guide_menu'.$key_suffix;
	    $games_hierarchy = $this->get($cache_key);

		// refresh cache if empty
		if (count($games_hierarchy) <= 0) {

			$games_hierarchy = $this->repository('Rproducts')
									->game_guide_menu(
										$disabled_IDs,
										$existing_menu,
										function(&$game){
							                $game['gameName']    = $this->service('Ssiteconfig')
								                                        ->gameName_unique_key(
								                                        	$game['gameID'],
								                                        	$game['gameName'],
								                                        	$game['productName']
								                                        );

							                $game['productName'] = $this->service('Ssiteconfig')
							                							->productName_formatter($game['productName']);

							                $game['productID']	 = $this->service('Scrypt')
														                ->crypt_encrypt($game['productID']);					

							            }
									);
			$this->put($cache_key, $games_hierarchy);

		}
		
		return $games_hierarchy;
    }

    /**
     * forget cache key
     * @param  string $key 
     * @return boolean      [description]
     */
    public function forget($key)
    {

    	$wrapped_key = $this->key_wrapper($key);
    	
    	if(Cache::has($wrapped_key)) {
    		
    		Cache::forget($wrapped_key);
    		return true;

    	} else {
    		
    		return false;

    	}
    }
}