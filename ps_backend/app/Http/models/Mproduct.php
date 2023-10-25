<?php

namespace Backend\models;

class Mproduct extends Basemodel {

    protected $table      = 'product';
    protected $hidden     = array();
    public    $timestamps = false;

    /**
     * Get basic product data
     * Specific
     * @param  int/mix $productID
     * @return object
     */
    public function get_product_data($productID) 
    {
        return $this->select('productName')->Bproduct_access_fields()->where('productID', '=', $productID)->first();
    }

    /**
     * get products data
     * List
     * @param  int/mix $productID
     * @return object
     */
    public function get_products_data($productIDs) 
    {
        return $this->select('productID','productName')
                    ->Bproduct_access_fields()
                    ->whereIn('productID', $productIDs)
                    ->get();
    }

    /**
     * This will get all missing productIDs
     * @param  array  $present_productIDs 
     * @return object
     */
    public function get_missing_productIDs($present_productIDs) 
    {
        
        return $this->whereNotIn('productID',$present_productIDs)->pluck('productID');

    }

    /**
     * This will get all missing productIDs
     * @param  array  $productName 
     * @return object
     */
    public function get_productID($productName) 
    {
        
        return $this->select('productID')->where('productName','=',$productName)->value('productID');

    }
    
    /**
     * get all products 
     * @return object
     */
    public function get_products()
    {
        return $this->select('productID','isCommRake')->get();
    }

    /**
     * This will get game info used to build game hierarchy
     * @param  array $disabled_IDs       array of disabled_gameIDs, disabled_productIDs, disabled_serverIDs
l
     * @return object
     */
    public function get_hierarchy($disbaled_IDs)
    {
        return $this->select('game.gameID','game.gameName','game.type','product.productName','game.productID')
                    ->join('game','game.productID','=','product.productID')
                    ->whereNotIn('product.productID', $disbaled_IDs['disabled_productIDs'])
                    ->whereNotIn('game.gameID', $disbaled_IDs['disabled_gameIDs'])
                    ->whereNotIn('game.serverID', $disbaled_IDs['disabled_serverIDs'])
                    ->orderBy('game.gameID')
                    ->get();
    }
}
