<?php

namespace Backend\models;

use DB;

class Mbanner extends Basemodel {

	protected $table = 'banner';
	protected $hidden = array('bannerID', 'adminID', 'productID', 'isMain', 'lastUpdate', 'startDate');
	public $timestamps = false;

	/**
	 * Filter all published banner items
	 * @param  object $query 
	 * @return object
	 */
	public function scopeActive($query)
	{

		$today = date('Y-m-d H:i:s');

		return $query->where('banner.startDate', '<=', $today)->where('banner.endDate', '>', $today);

	}

	/**
	 * This will filter all banner items by whiteLabelID
	 * @param  object $query      
	 * @param  string $whiteLabelID 
	 * @return object  
	 */
	public function scopewhiteLabelID($query, $whiteLabelID)
	{

		if ($whiteLabelID == '') {
			
			return $query->whereNull('banner.whiteLabelID');

		} else {

			return $query->where('banner.whiteLabelID','=',$whiteLabelID);

		}
	}

	/**
	 * This will filter promotion items
	 * @param  object $query 
	 * @return object
	 */
	public function scopePromotion($query)
	{

		return $query->where('banner.isPromotion','=',1);

	}

	/**
	 * This will filter banners items
	 * @param  object $query 
	 * @return object
	 */
	public function scopeBanner($query)
	{

		return $query->where('banner.isBanner','=',1);

	}

	/**
	 * This will filter side banners items
	 * @param  object $query 
	 * @return object
	 */
	public function scopeSide_banner($query)
	{

		return $query->where('banner.isSideBanner','=',1);

	}

	/**
	 * This will modify the isNew field
	 * @param   object $query
	 * @return  object
	 */
	public function scopeisNew_field($query, $action = 'select' )
	{
		$raw = 'CASE WHEN(banner.isNew = 1 AND banner.lastUpdate + INTERVAL 2 WEEK >= ?) 
					THEN 1 
					ELSE 0
				END';

		switch ($action) {
			case 'where': 
				return $query->whereRaw("{$raw} = ?")->addBinding(array(date('Y-m-d H:i:s'), 1),$action);

			default     : 
				return $query->addSelect(DB::raw("{$raw} as derived_isNew"))->addBinding(date('Y-m-d H:i:s'),$action);
		}
	}

	/**
	 * This will add the order by clause 
	 * @param  object  $query
	 * @return object
	 */
	public function scopeAdd_ordering($query)
	{

		return $query->orderBy('order', 'asc')
			->orderBy('derived_isNew', 'desc')
			->orderBy('lastUpdate', 'desc')
			->orderBy('startDate', 'desc');

	}

	/**
	 * This will count new promos per whiteLabelID
	 * @param  string $whiteLabelID 
	 * @return int
	 */
	public function count_promotions($whiteLabelID)
	{

		return $this->promotion()->whiteLabelID($whiteLabelID)->active()->count('banner.bannerID');

	}

	/**
	 * This will get banners of whiteLabelID but limited to allowable count per productID
	 * @param  string $whiteLabelID          
	 * @param  array  $allowed_per_productID [productID => limit]
	 * @return object
	 */
	public function get_banners($whiteLabelID, $limit_per_productID)
	{	

		$union_query = null;

		foreach ($limit_per_productID as $productID => $limit) {

			$query = $this->select(
						'banner.productID',
						'banner.order', 
						'banner.videoSource', 
						'banner.title', 
						'banner.previewText', 
						'banner.position',
						'banner.btnLink',
						'banner.btnText',
						'banner.imgBanner as path',
						'banner.lastUpdate',
						'banner.startDate'
					)
					->isNew_field()
					->where('banner.productID','=',$productID)
					->whiteLabelID($whiteLabelID)
					->banner()
					->active()
					->add_ordering()
					->take($limit);

			if ($union_query == null) {

				$union_query = $query;

			} else {

				$union_query->union($query);

			}

		}

		return $union_query->add_ordering()->get();
		
	}

	/**
	 * This will get all sidebanner for given whiteLabelID
	 * @param  sreing $whiteLabelID 
	 * @param  int    $limit      
	 * @return object
	 */
	public function get_side_banners($whiteLabelID, $limit)
	{

		return $this->select('banner.imgBanner as path')
					->isNew_field()
					->whiteLabelID($whiteLabelID)
					->side_banner()
					->active()
					->add_ordering()
					->take($limit)
					->get();


	}

	/**
	 * get promotions
	 * @param  string $whiteLabelID 
	 * @return array of object      base query of promotions
	 */
	public function scopePromotions_info_fields($query, $whiteLabelID)
	{

		return $query->addSelect(
				'banner.bannerID as bid',
				'banner.productID',
				'banner.imgPromotion as promo_path',
				'banner.title',
				'banner.previewText',
				'banner.description',
				'banner.videoSource'
			)
			->whiteLabelID($whiteLabelID);

	}

	/**
	 * scope for searching promotions
	 * @param  object $query  
	 * @param  string $search 	
	 * @return object         
	 */
	public function scopeSearch($query, $search)
	{

		return $query->where('banner.description', 'LIKE', '%' . $search . '%');

	}

	/**
	 * get all promotions of whitelabel
	 * @param  string $whiteLabelID 
	 * @param  array $offset       skip, take
	 * @return array               
	 */
	public function get_promotions($whiteLabelID, $limit_offset)
	{

		return $this->promotions_info_fields($whiteLabelID)
					->isNew_field()
					->active()
					->promotion()
					->add_ordering()
					->Boffsets($limit_offset)
					->get();

	}

	/**
	 * get all new promotions
	 * @param  string $whitelabelID
	 * @param  array $offset  skip, take     
	 * @return array             
	 */
	public function get_new_promotions($whiteLabelID, $limit_offset)
	{
		return $this->promotions_info_fields($whiteLabelID)
					->isNew_field()
					->active()
					->promotion()
					->add_ordering()
					->Boffsets($limit_offset)
					->isNew_field('where')
					->get();

	}

	/**
	 * search promotions
	 * @param  string $whiteLabelID 
	 * @param  array $offset       skip, take
	 * @param  string $search       
	 * @return array               
	 */
	public function search_promotions($whiteLabelID, $limit_offset, $search)
	{
		return $this->promotions_info_fields($whiteLabelID)
					->isNew_field()
					->active()
					->promotion()
					->add_ordering()
					->search($search)
					->Boffsets($limit_offset)
					->get();
	}
}
