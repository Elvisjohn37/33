<?php 
$config['menu']['primary'] = array(
				0 => array(
					'text'		=>'',
					'page'		=>'banner',
					'id'		=>'home',
				),
				5 => array(
					'text'		=> '{{@lang.language.sports}}',
					'page'		=> 'sports',
					'id'		=> 's_sports',
					'floating'       => true,
                    'direct_trigger' => true,
			    	'featured'  => true
				),
				6 => array(
			    	'text'		     => '{{@lang.language.live_casino}}',
			    	'page'		     => 'live_casino',
			    	'id'		     => 'live_casino',
			    	'featured'       => array('soon' => true)
				),
				3 => array(
			    	'text'      =>'{{@lang.language.tangkas}}',
			    	'page'      =>'tangkas',
			    	'id'        =>'tangkas',
			    	'featured'  => array('soon' => false)
				),
				1 => array(
			    	'icon'      => '',
			    	'text'      => '{{@lang.language.games}}',
			    	'page'      => 'games',
			    	'id'        => 'games',
			    	'has_badge' => true,
			    	'featured'  => array('soon' => false)
				),
				4 => array(
			    	'text'      => '{{@lang.language.live_togel}}',
			    	'page'      => 'live_togel',
			    	'id'        => 'live_togel',
			    	'featured'  => array('soon' => false)
				),
				7 => array(
                	'text'     =>'{{@lang.language.multiplayer}}',
                	'page'     =>'multiplayer',
                	'id'       =>'multiplayer',
                	'has_badge' => true,
                    'featured' => array('soon' => false)
				),
				101 => array(
			    	'text'      => '{{@lang.language.promotions}}',
			    	'page'      => 'promo',
			    	'id'        => 'promo',
               		'has_badge' => true,
			    	'featured'  => array('soon' => false)
				),
				102 => array(
					'text' => '{{@lang.language.tournament}}',
					'page' => 'tournament',
					'id'   => 'tournament'
				)
			);