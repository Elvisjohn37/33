<?php
    $config = array(
				# Other js, css, and meta file links are in global.static_assets.blade
				'from' => 'homepage',

				# Other constant plugins are already declared in plugins.loader
				'plug'=> array(),

				# menu = array(<icon name>,<text>,<page to show>,id extension,counter index separated by ':')
				# menu[id] pattern should be snake cased productName
				# We cant make it dynamic to current productName in DB because blade template file is based on it.
				# If  prod DB changed productName on the fly, 
				# we should still use the old name until the blade template file name is changed
				'menu' =>array(
					'primary' => array(
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
								),

			        'login_form' => array(

			            109 => array(
			                    'text'     =>'{{@lang.language.forgot_password}}',
			                    'page'     =>'forgot_password',
			                    'id'       =>'forgot_password',
			                    'floating' => true
			                )
			        ),

			        'hidden' => array(
			            108 => array(
			                    'text'     =>'{{@lang.language.register}}',
			                    'page'     =>'register',
			                    'id'       =>'register',
			                    'floating' => true
			                ),
                        111 => array(
                                'text'     => 'Error',
                                'page'     => 'error_page',
                                'id'       => 'error_page',
                                'floating' => true
                            )
			        )
					
				),

		    /*
		    |-------------------------------------------------------------------------------------------------------------------
		    | Sidebars
		    |-------------------------------------------------------------------------------------------------------------------
		    |
		    | Set sidebars for each menu ID
		    | This sidebars will still being filtered by Ssiteconfig service.
		    | This means even if you put sidebar on this list, it might still be 
		    | removed by the service.
		    | <menuID> => array(<sidebars>)
		    | 
		    */
		    'sidebars' => array(
		                    106 => array(

		                        array(
		                            'text'     => '{{@lang.language.faq}}',
		                            'id'       => 'faq',
		                            'children' => array(
		                                            array(
		                                                'id'        => 'general', 
		                                                'productID' => 0
		                                            )
		                                        )
		                        ),

		                        array(
		                            'text'     => '{{@lang.language.gaming_rules}}',
		                            'id'       => 'gaming_rules',
		                            'children' => array(
		                                            array(
		                                                'id'        => 'general', 
		                                                'productID' => 0
		                                            )
		                                        )
		                        ),

		                        array(
		                            'text' => '{{@lang.language.game_guide}}',
		                            'id'   => 'game_guide'
		                        ),

		                        array(
		                            'text' => '{{@lang.language.terms_and_conditions}}',
		                            'id'   => 'terms_and_conditions'
		                        ),

		                        array(
		                            'text' => '{{@lang.language.contact_us}}',
		                            'id'   => 'contact_us'
		                        )
		                        
		                    )
		                ),
				
				'detect_timeout' => false,

				/*
			    |--------------------------------------------------------------------------
			    | plugin_cache
			    |--------------------------------------------------------------------------
			    |
			    | List which plugins should be cacheable and non_cacheable
			    |
			    */
				'plugin_cache' 	 => array(
					'enable'  => array('news','transactions','jackpot','lastresult','badge','banner'),
					'disable' => array()
				),

			    /*
			    |--------------------------------------------------------------------------
			    | chat
			    |--------------------------------------------------------------------------
			    |
			    | chatbox settings
			    |
			    */
				'chat' =>  array(
			                'type'    => 'native',
			                'license' =>  4721851
					    )
			);