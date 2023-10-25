<?php

namespace Backend\repositories;
use DateTime;
use Input;
use DB;
use Layer;

/**
* Repositories for all table connected to client data
*/
class Rwhitelabel extends Baserepository
{

	public $models = array(
			            'Mwhitelabel',
			            'Mwhitelabelchatapp',
			            'Mbank',
			            'Mnews',
			            'Mbanner',
			            'Mcurrency'
					);

	/**
	 * get latest news
	 * @param  array  $news_config 
	 * @param  string $whiteLabelID
	 * @param  string $language
	 * @return array
	 */
	public function get_news($news_config, $whiteLabelID, $language)
	{

		$news_items = $this->model('Mnews')->get_latest($news_config['count'],$whiteLabelID, $language);

        foreach ($news_items as $news_item) {

            $news_item->content = escape_string($news_item->content);
            $news_item->title  	= escape_string($news_item->title);

            // remove any tags
            $news_item->preview = strip_tags($news_item->content);

            // cur data to required preview only
            $news_item->preview = str_limit($news_item->preview, $news_config['preview_characters']);

            // format date
            if (isset($news_config['date_format'])) {
            	$news_item->lastUpdate = custom_date_format($news_config['date_format'], $news_item->lastUpdate);
            }

        }

        return $news_items->toArray();

	}

	/**
	 * This will get how many new promos per whiteLabelIDS
	 * @param  string $whiteLabelID 
	 * @return int
	 */
	public function count_promotions($whiteLabelID)
	{

		return $this->model('Mbanner')->count_promotions($whiteLabelID);

	}

	/**
	 * This will get banners for each allowed banners count per productID
	 * @param  string $whiteLabelID          
	 * @param  array  $banners_per_productID [productID => banner count]
	 * @param  array  $menu_list     		 We will assign menu id as banners product name
	 * @return array
	 */
	public function get_banners($wl_banner_config, $menu_list, callable $encrypt)
	{

		$banners = $this->model('Mbanner')->get_banners(
												$wl_banner_config['whiteLabelID'],
												$wl_banner_config['limit_per_productID']
											);

		foreach ($banners as $banner) {

			if (isset($menu_list[$banner->productID])) {
				
				$banner->promo_for = $menu_list[$banner->productID]['id'];

			} else {

				if (is_callable($encrypt)) {
					// Not in the menu, it will be displayed to home only, no need for actual ID
					$banner->promo_for = $encrypt($banner->productID);
				}

			}

		}

		return $banners->toArray();

	}

	/**
	 * This will get white labels side banner
	 * @param  string $whiteLabelID          
	 * @param  array  $limit 		 count of maximum side banners we need to get
	 * @return array
	 */
	public function get_side_banners($whiteLabelID, $limit)
	{

		return $this->model('Mbanner')->get_side_banners($whiteLabelID, $limit)->toArray();

	}

	/**
	 * This will get white labels chat supports
	 * @param  string $whiteLabelID          
	 * @return array
	 */
	public function get_customer_supports($whiteLabelID)
	{

		$chat_supports = $this->model('Mwhitelabelchatapp')->get_customer_supports($whiteLabelID);

		foreach ($chat_supports as $chat_app) {

            $chat_app->content = escape_string($chat_app->content);
            $chat_app->status  = $chat_app->status === 'Online';

        }

        return $chat_supports->toArray();

	}

	/**
	 * This will get bank supports of given whiteLabelID
	 * @param  string  $whiteLabelID   [description]
	 * @param  boolean $is_get_details [description]
	 * @return array
	 */
	public function get_bank_supports($whiteLabelID, $is_get_details)
	{

		$bank_supports_raw = $this->model('Mbank')->get_bank_support($whiteLabelID, $is_get_details);
		
		$today                               = date('Y-m-d H:i:s');
		$bank_supports_formatted             = array();
		$processed_whiteLabelBankAccountIDs  = array();
		$processed_whiteLabelBankScheduleIDs = array();

		foreach ($bank_supports_raw as $bank) {

	        /*
	         |----------------------------------------------------------------------------------------------------------
	         | Bank Basic info
	         |----------------------------------------------------------------------------------------------------------
	         | One instance per bank only
	         */
			$bank_array_key    = to_snake_case(escape_string($bank->bankName));

            if (!isset($bank_supports_formatted[$bank_array_key])) {
            	
				$bank->contentText = escape_string($bank->contentText);
            	$bank_supports_formatted[$bank_array_key] = array(
											        		'bank'        => $bank_array_key, 
											        		'status'      => $bank->isOnline, 
											        		'description' => $bank->contentText,
											        		'accounts'    => array()
											        	);

            }

	        /*
	         |----------------------------------------------------------------------------------------------------------
	         | Bank Account
	         |----------------------------------------------------------------------------------------------------------
	         */
	        if (!empty($bank->whiteLabelBankAccountID) && !empty($bank->accountNumber)) {
	        	if (!in_array($bank->whiteLabelBankAccountID,$processed_whiteLabelBankAccountIDs)) {
	        		// add details to list
		            $bank->accountName 	 = escape_string($bank->accountName);
		            $bank->accountNumber = escape_string($bank->accountNumber);
	        		$bank_supports_formatted[$bank_array_key]["accounts"][] = array(
														        				'name' 	 => $bank->accountName, 
														        				'number' => $bank->accountNumber
														        			);

					$processed_whiteLabelBankAccountIDs[] = $bank->whiteLabelBankAccountID;
	        	}
	        }

	        /*
	         |----------------------------------------------------------------------------------------------------------
	         | Bank Schedule
	         |----------------------------------------------------------------------------------------------------------
	         | No need to check downtime if its already down
	         */
	        if (!empty($bank->whiteLabelBankScheduleID) && $bank_supports_formatted[$bank_array_key]['status'] != 0) {

	        	if (!in_array($bank->whiteLabelBankScheduleID, $processed_whiteLabelBankScheduleIDs)) {

	        		$forceValidityDate = custom_date_format('Y-m-d H:i:s', $bank->forceValidityDate);

			        if ($today >= $forceValidityDate && $bank->isOnline != 0 ) {

						$is_down = on_weekly_schedule(array(

			        				'startDay'	=> $bank->startDay,
			        				'startTime' => $bank->startTime,
			        				'endDay' 	=> $bank->endDay,
			        				'endTime'	=> $bank->endTime

			        			));

						$bank_supports_formatted[$bank_array_key]['status'] = $is_down ? 0 : $bank->isOnline;

			        } else {

			        	$bank_supports_formatted[$bank_array_key]['status'] = $bank->isOnline;

			        }

					$processed_whiteLabelBankScheduleIDs[] = $bank->whiteLabelBankScheduleID;

			    }
	        }
		}

		return array_values($bank_supports_formatted);
	}
	
	/**
	 * Get promotions of whitelabel
	 * @param  string $whiteLabelID 
	 * @param  array $filters       [$take, $page use for offset
	 *                              $filter, $search]
	 * @return array               
	 */
	public function get_promotions($filters, $limit_offset, callable $encrypt)
	{

        $filters['filter'] = (trim($filters['search']) !== '') ? 'search' : $filters['filter'];
        
        switch (strtolower($filters['filter'])) {

        	case 'new':
        		
        		$promotions = $this->model('Mbanner')->get_new_promotions($filters['whiteLabelID'],$limit_offset);

        		break;

        	case 'search':
        		
        		$promotions = $this->model('Mbanner')->search_promotions(
			        			$filters['whiteLabelID'],
								$limit_offset,
			    				$filters['search']
			    			);

        		break;


        	default:

        		$promotions = $this->model('Mbanner')->get_promotions($filters['whiteLabelID'],$limit_offset);

        		break;
        }
	
		foreach ($promotions as $key => $promotion) {

			$promotion->path                  = $promotion->promo_path;
			if (is_callable($encrypt)) {
	        	$encrypt($promotion);
			}
			$promotion->description           = nl2p($promotion->description);

		}

		$return['total'] = count($promotions);
		$return['rows']  = $promotions;

		return  $return;

	}

    /**
     * compose accountNumber 
     * based on the required number of segments
     * 
     * @param array $bank_input
     * @param string $bankName
     * @return array
     */
    public function format_bank_number ( $bank_input, $bankName ) 
    {
        $bank_pattern = $this->model('Mbank')->get_bank_number( $bankName );

        if ($bank_pattern) {
        
            $bank_pattern_info = $this->get_accountNoPattern_info(
            						$bank_pattern->accountNoPattern,
            						$bank_pattern->accountBankNo
            					);

            $accountNumber     = array();

            // restrict to how many segments are required
            for ($segment = 0; $segment < $bank_pattern_info['segment_count']; $segment++) {

                $accountNumber[$segment] = $bank_input[$segment];
            }
            
            $accountNumber = implode('-',$accountNumber);
            
            return compact('bank_pattern_info','accountNumber');
        }

        return FALSE;
    }

    /**
     * This will get detailed information of specific bank
     * 
     * @param  string $accountNoPattern  regex
     * @param  string $accountBankNo     sample bank number
     * @return array
     */
    private function get_accountNoPattern_info($accountNoPattern, $accountBankNo)
    {
        $parsed_accountNoPattern = substr($accountNoPattern, 2, -2);
        $parsed_accountNoPattern = str_replace('}-[','}-==-[',$parsed_accountNoPattern);
        $segment_regex	         = explode('-==-', $parsed_accountNoPattern);
        $segment_count           = count($segment_regex);

        $parsed_accountBankNo    = explode('-', $accountBankNo);
        $segment_length          = array();

        for ($segment=0; $segment<$segment_count; $segment++) {
        	// get the length per segments
        	array_push($segment_length,strlen($parsed_accountBankNo[$segment]));

        	// remove the characater length delimeter of the regex
        	$segment_regex[$segment] = '^'.preg_replace('/\{\d+\}/', '', $segment_regex[$segment]).'*$';
        }

        return compact('segment_regex','segment_count','accountNoPattern','segment_length');
    }
    
    /**
     * This will get all banks valid for players selection
     * @param type $whiteLabelID
     * @return array
     */
    public function get_bank_dropdown($whiteLabelID)
    {
        $banks = $this->model('Mbank')->get_wl_banks($whiteLabelID)->toArray();
        
        $dropdown   = array();
        
        foreach ($banks as &$bank) {

            if (!isset($dropdown[$bank['bankName']])) {
            	
            	$dropdown[$bank['bankName']] = $this->get_accountNoPattern_info(
            										$bank['accountNoPattern'],
            										$bank['accountBankNo']
            									);

            }

        }
        
        return $dropdown;
    }
    
    /**
     * Check if currencyID can be use to signup
     * @param  int $currencyID
     * @return type
     */
    public function check_webSignupEnabled($currencyID)
    {
        $check = $this->model('Mcurrency')->count_webSignupEnabled($currencyID);
        
        return ($check > 0) ? TRUE : FALSE;
    }

    /**
     * This will get bank minimum Deposit Limit
     * @param  string $bankName
     * @param  string $whiteLabelID
     * @return array
     */
    public function get_bank_minDepositLimit($bankName, $whiteLabelID)
    {
    	$bank = $this->model('Mbank')->get_minDepositLimit($bankName, $whiteLabelID);

    	if ($bank) {

    		return $bank->minDepositLimit;

    	} else {

    		return 0;

    	}
    }

    /**
     * Get all currencies available for signup
     * @return type
     */
    public function currency_dropdown($currency_config)
    {	
    	if ($currency_config['whiteLabelID'] == '') {
	    	$currencies = $this->model('Mcurrency')->get_webSignupEnabled_currency($currency_config['registration_parents']);

    	} else {
	    	$currencies = $this->model('Mcurrency')->get_agentSignup_currency($currency_config['whiteLabelID']);
    	}

       	$formatted_currencies = array();

        foreach ($currencies as $currency) {
        	$formatted_currencies[$currency->currencyID] = $currency->description.'('.$currency->code.')';
        }

        return $formatted_currencies;
    }

    /**
     * get chat application ofr whitelabel
     * @param  string $whiteLabelID
     * @return array               
     */
    public function get_chat_app($whiteLabelID)
    {

    	if (isset($whiteLabelID)) {

	    	$chat = $this->model('Mwhitelabelchatapp')->chat_by_whitelabelID($whiteLabelID);

    	} else {

    		$chat = $this->model('Mwhitelabelchatapp')->get_chat_apps();
    	
    	}

    	return $chat ? $chat->toArray() : array();
    	
    }
}