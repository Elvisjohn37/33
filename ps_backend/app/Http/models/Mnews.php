<?php

namespace Backend\models;

class Mnews extends Basemodel {

	protected $table      = 'news';
	public    $timestamps = false;
	protected $hidden     = array('newsID', 'adminID');
	protected $guarded    = 'newsID';

	/**
	 * This will help filter news on a given whiteLabelID
	 * @param  object $query        
	 * @param  string $whiteLabelID
	 * @return object
	 */
	public function scopewhiteLabelID($query, $whiteLabelID)
	{
		
		$query = $query->Join('admin','admin.adminID','=','news.adminID')
					->leftJoin("whitelabel",function($join){ 

						$join->on('whitelabel.adminID','=','admin.adminID')
						->orOn('whitelabel.adminID','=','admin.parentID');

					});

		if ($whiteLabelID == '') {
				
			return $query->whereNull('whitelabel.whiteLabelID');

		} else {

			return $query->where('whitelabel.whiteLabelID','=',$whiteLabelID);

		}


	}

	/**
	 * Filter all news that's already good for publication
	 * @param  object $query 
	 * @return object
	 */
	public function scopepublished($query)
	{

		return $query->where('startDate','<=',date('Y-m-d H:i:s'));

	}

	/**
	 * This will help us get all latest news in speciific whiteLabelID
	 * @param  int    $take_count   number of latest news the request want to fetch
	 * @param  string $upto_date    the maximum date of news to be fetched
	 * @param  string $whiteLabelID whiteLabelID
	 * @return object
	 */
	public function get_latest($take_count, $whiteLabelID, $language)
	{
		
		return $this->select("news.title", "newscontent.content", "news.lastUpdate")
					->join('newscontent','newscontent.newsID', '=', 'news.newsID')
					->whiteLabelID($whiteLabelID)
					->published()
					->orderby('startDate','desc')
					->where('newscontent.languageCode','=', $language)
					->take($take_count)
					->get();
	}

}
