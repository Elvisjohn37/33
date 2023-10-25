<?php
namespace Backend\models;

use Illuminate\Database\Eloquent\Model;
use Backend\contracts\Slayerinterface;
use Layer;
use DB;

/**
 * Main objective of this base is to set rules for models
 * You can also put here some scope, methods, variables that is used by 2 or more models
 */
class Basemodel extends Model  implements Slayerinterface {
	
	public $BnotificationStatusIDs      = array( 
	    									'approved' => array(1,4),
									        'denied'   => array(2,3)
									    );
	public $Bjoinable_walletDs          = array(2);
    public $Btransfer_report_pending 	= array('withdrawal','deposit','cutoff');
    public $Btransactiondetail_dateTime = array('settled' => 'endDateTime','unsettled' => 'startDateTime');

    public $Btransactiondetail_grouping = 	"CONCAT(
												CASE WHEN transactiondetail.event='V' THEN 1 ELSE 0 END,
												CASE WHEN transactiondetail.gameID = 3 
													THEN txnID 
													ELSE 
														CASE WHEN transactiondetail.gameID = 20001 
															THEN txnDetID 
															ELSE transactiondetail.transactionDetID  
														END 
													END
											)";

	public $Bclient_status_query        =	"CASE WHEN client.companySettingID != 0 
				                                THEN  CONCAT('com_',client.companySettingID)
				                                ELSE  CONCAT('mem_',client.memberStatusID)
				                            END";

	public $Bas_transactiondetail_dateTime = "CASE WHEN(transaction.transactionType='Bet')
												THEN transactiondetail.startDateTime
												ELSE transactiondetail.endDateTime
											END as dateTime";

	public $Bclient_transactable_query;
	public $Bclient_active_query;
    
    public function __construct() 
    {

    	// define variables that is dependent to others
		$this->Bclient_transactable_query = "CASE WHEN ".$this->Bclient_status_query." = 'mem_1'
							                    THEN  1
							                    ELSE  0
							                END";

					                            
		$this->Bclient_active_query   	  = "CASE WHEN ".$this->Bclient_status_query." IN('mem_1','mem_2')
							                    THEN  1
							                    ELSE  0
							                END";
    }
	
	public function model($service_name) 
	{

		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $service_name

		));
		
	}

	public function service($service_name) 
	{

		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $service_name

		));
		
	}

	public function controller($controller_name) 
	{

		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $controller_name

		));
		
	}

	public function repository($repository_name) 
	{
		
		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $repository_name

		));
		
	}

	public function library($library_name) 
	{
		
		Layer::forbidden(array(

			'layer'  => __FUNCTION__,
			'caller' => $this->caller,
			'called' => $library_name

		));
		
	}

	/**
	 * This will add the aggregate to the field 
	 * If the alias is not a string it will take the last segment of field instead
	 * example: 
	 * 		1. 
	 * 			aggregate = sum
	 * 			field  	  = array('alias' => 'table.column');
	 *          result    = SUM(table.column) as alias
	 *      2. 
	 * 			aggregate = sum
	 * 			field  	  = array('table.column');
	 *          result    = SUM(table.column) as column
	 *          
	 * @param  object $query 
	 * @param  array  $fields 
	 * @return 
	 */
	public function scopeBaggregate_fields($query, $aggregate, $fields)
	{	
		// build query
		$toupper_aggregate = strtoupper($aggregate);
		$select_build = '';

		foreach ($fields as $alias => $field) {
			
			if ($select_build!='') {

				$select_build.=',';

			}

			if (!is_string($alias)) {
				
				$alias = get_last_segment($field);

			}

			// make sure to convert any space to underscore
			$alias = to_snake_case($alias, false);

			$select_build .= $toupper_aggregate.'('.$field.') as '.$alias;

		}

		return $query->addSelect(DB::raw($select_build));
	}

	/**
	 * Filter all approved notification status of transfer
	 * @param  object $query
	 * @return object
	 */
	public function scopeBapproved_transfer($query)
	{

		return $query->whereIn('transfer.notificationStatusID', $this->BnotificationStatusIDs['approved']);

	}

	/**
	 * This will filter settled bets only
	 * @param  object $query 
	 * @return object
	 */
	public function scopeBsettled_bet($query)
	{
		return $query->where('transactiondetail.event','!=','R');
	}

	/**
	 * This will filter unsettled bets only
	 * @param  object $query 
	 * @return object
	 */
	public function scopeBunsettled_bet($query)
	{
		return $query->where('transactiondetail.event','=','R');
	}

	/**
	 * This will add grouping column for betting types
	 * @param  object $query 
	 * @param  object $used_for
	 * @return object
	 */
	public function scopeBbet_grouping_field($query, $used_for = '')
	{
		switch($used_for) {

			case 'count': 

				return $query->Baggregate_fields('COUNT', 
						array('derived_bet_grouping' => Db::raw('DISTINCT '.$this->Btransactiondetail_grouping))
					);

			default:

				$query->addSelect(
					Db::raw($this->Btransactiondetail_grouping.' as derived_bet_grouping')
				);

				if ($used_for!='') {

					return $query->$used_for('derived_bet_grouping');

				} else {

					return $query;

				}

		}
	}

	/**
	 * This will get all active transfer base on notificationStatusID
	 * @param  object $query 
	 * @return object       
	 */
	public function scopeBactive_transfer($query)
	{	
		return $query->where(function($query) {
			
			$query->whereNotIn('transfer.notificationStatusID',$this->BnotificationStatusIDs['denied'])

				->where(function($where){

		 	   		$where = $this->scopeBapproved_transfer($where)
		 	   					->orWhereIn('transfer.type', $this->Btransfer_report_pending);
		 	   					

		 	    });

		});
	}
	
	/**
     * This will add derived_joinable, if walletID is joinable and client status is transactable
     * @param  object $query 
     * @return object       
     */
    public function scopeBjoinable_wallet_field($query)
    {
        return $query->addSelect(
                DB::raw(
                    'CASE 
                        WHEN '.$this->table.'.walletID IN(?) AND '.$this->Bclient_transactable_query.' = 1 THEN 1
                        ELSE 0
                    END as derived_joinable'
                )
            )->addBinding(implode($this->Bjoinable_walletDs,','), 'select');
    }

	/**
     * This will add derived_joinable_wallet, if walletID is joinable only regardless of client status
     * @param  object $query 
     * @return object       
     */
    public function scopeBjoinable_walletID_field($query)
    {
        return $query->addSelect(
                DB::raw(
                    'CASE 
                        WHEN '.$this->table.'.walletID IN(?) THEN 1
                        ELSE 0
                    END as derived_joinable_wallet'
                )
            )->addBinding(implode($this->Bjoinable_walletDs,','), 'select');
    }

    /**
     * This will add joinable field
     * @param  object $query 
     * @return object       
     */
    public function scopeBtotal_transactions_field($query)
    {
        return $query->Baggregate_fields('COUNT', array(
			'derived_total_transactions' => 'transactiondetail.txnID'
		));
    }

	/**
	 * This is like eloquent firstorcreate with 2nd argument as additional data when inserting
	 * @param  object $query      
	 * @param  array  $attributes 
	 * @param  array  $values     
	 * @return int                primaryKey
	 */
	public function BfirstOrCreate($attributes, $values = false)
	{
		$result = $this->firstOrNew($attributes);

		if (!$result->exists) {

			$result = $this->Binsert(assoc_array_merge($attributes, $values), false);
			$result->exists = false;
			
		}

		$primaryKey  = $this->primaryKey;

		if ($result->$primaryKey) {

			$result->id = $result->$primaryKey;
		}

		return $result;
	}

	/**
	 * This is like eloquent firstorcreate with 2nd argument as additional data when inserting
	 * This will use 'or' in where clause of finding the attributes
	 * Sample: where attribute1 = attribute1 value or attribute2 = attribute2 value
	 * @param  object $query      
	 * @param  array  $attributes 
	 * @param  array  $values     
	 * @return int                primaryKey
	 */
	public function Bcontains_or_create($attributes, $values = false)
	{
		$result = $this->orWhere_multi($attributes)->first();

		if ($result) {

			$result->exists = true;

		} else {

			$result = $this->Binsert(assoc_array_merge($attributes, $values), false);
			$result->exists = false;

		}

		$primaryKey  = $this->primaryKey;

		if ($result->$primaryKey) {

			$result->id = $result->$primaryKey;
		}

		return $result;

	}

	/**
	 * This will add multiple where clause using 'or'
	 * @param  object $query    
	 * @param  array  $attributes 
	 * @return array
	 */
	public function scopeOrWhere_multi($query, $attributes)
	{
		return $query->where(function($where) use($attributes) {

			foreach ($attributes as $attribute => $value) {
				
				$where->orWhere($attribute,'=',$value);

			}

		});
	}

	/**
	 * This will insert attibutes to given class then return id
	 * @param  array   $attributes
	 * @param  boolean $id_only     (optional) default = true, this will return primary key only if set to true
	 *                              else this will return full result
	 * @return int
	 */
	public function Binsert($attributes, $id_only = true)
	{
		$class = get_called_class();

		$primaryKey  = $this->primaryKey;

		// save
		$result = new $class;

		foreach ($attributes as $name => $value) {
			
			$result->$name = $value;

		}

		$result->save();

		// get the id
		if ($result->$primaryKey) {

			$result->id = $result->$primaryKey;

		}

		// reponse
		if ($id_only) {

			return $result->id;

		} else {

			return $result;

		}
	}

	/**
	 * This will add fields that is needed for product access validation
	 * NOTE: If you add new field here and the field has similar name in game table
	 * 		 Please add prefix 'product_<field>' 
	 * @param  object $query 
	 * @return object
	 */
	public function scopeBproduct_access_fields($query)
	{
		return $query->addSelect('product.isTestModeEnabled as product_isTestModeEnabled');
	}

	/**
	 * Add offsets to query, skip and take if values are not null
	 * @param  object $query 
	 * @param  array  $offsets [offset, limit]
	 * @return object
	 */
	public function scopeBoffsets($query, $offsets)
	{
		if (is_array($offsets)) {

			if (!empty($offsets['offset'])) {
				$query->skip($offsets['offset']);
			}

			if (!empty($offsets['limit'])) {
				$query->take($offsets['limit']);
			}

		}

		return $query;
	}
}
