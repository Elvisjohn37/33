<?php

namespace Backend\models;

use DB;

class Mgame extends Basemodel  {

    protected $table        = 'game';
    protected $primaryKey   = 'gameID';
    protected $hidden       = array(
                                'productID',
                                'serverID',
                                'reelComposition',
                                'denomination',
                                'defaultLineSelected',
                                'defaultBetSelected',
                                'RTP',
                                'maxLine',
                                'maxPayout',
                                'maxBet',
                                'featurePayout',
                                'payLine',
                                'paySymbol',
                                'payTable',
                                'creditBPL',
                                'lastChangeDate',
                                'releaseDate',
                                'minBet',
                                'swfName',
                                'isTestModeEnabled'
                            );
    public $timestamps      = false;

    
    /**
     * This will add fields that is needed for game access validation
     * NOTE: If you add new field here and the field has similar name in product table
     *       Please add prefix 'game_<field>' 
     * @param  object $query 
     * @return object
     */
    public function scopeGame_access_fields($query)
    {
        return $query->addSelect(
            'game.isTestModeEnabled as game_isTestModeEnabled', 
            'game.isMobileReady',
            'game.productID',
            'product.isMultipleInstance'
        );
    }
    
    /** 
     * This will get basic game data base on gameID
     * @param  object $query  
     * @param  string $gameID
     * @return object
     */
    public function scopeGame_data($query, $gameID)
    {
        return $query->addSelect(
            'game.gameID',
            'game.serverID',
            'game.gameName',
            'product.productName'
        )
        ->Bproduct_access_fields()
        ->game_access_fields()
        ->product_join()
        ->gameID($gameID)
        ->first();
    }

    /**
     * This will get game details
     * @param  type $gameID
     * @return type
     */
    public function get_game_data($gameID)
    {
        return $this->game_data($gameID);
    }

    /**
     * This will get game details and its url according to platform
     * @param type $gameID
     * @param  string $platform 
     * @return type
     */
    public function game_data_url($gameID, $platform)
    {
        return $this->select('gamepath.url')
                    ->join('gamepath', function($join) use($platform) {
                        $join->on('gamepath.gameID','=','game.gameID')->on('gamepath.platform','=',DB::raw('?'));
                    })
                    ->addBinding(array($platform), 'join')
                    ->game_data($gameID);
    }

    /**
     * This will join product table to game table
     * @param  object $query
     * @return object
     */
    public function scopeproduct_join($query)
    {
        return $query->Join('product', 'game.productID', '=', 'product.productID');
    }
            
    /**
     * get all games
     * 
     * @param type $productID
     * @param type $is_order
     * @return type
     */
    public function get_all($productID, $is_order, $serverIDs) 
    {
        
        return $this->game_info_fields($is_order)
                    ->productID($productID)
                    ->serverIDs($serverIDs)
                    ->get();
    }

    /**
     * get all new games 
     * 
     * @param type $productID
     * @param type $is_order
     * @return type
     */
    public function get_new($productID, $is_order = true, $serverIDs)
    {
        return $this->game_info_fields($is_order)
                    ->productID( $productID )
                    ->serverIDs($serverIDs)
                    ->new()
                    ->get();
                    
    }
    
    /**
     * Search game
     * 
     * @param obj $query
     * @param string $gameName
     * @return obj
     */
    public function search_game($options, $productID, $is_order = true)
    { 
        return $this->game_info_fields($is_order)
                    ->where('gameName', 'LIKE', "%{$option['gameName']}%")
                    ->productID($productID)
                    ->serverIDs($option['serverIDs_disabled'])
                    ->get();
    }
    
    /**
     * This will count all new games in
     * 
     * @param  $productID 
     * @return int
     */
    public function count_new( $productID, $serverIDs ) 
    {
        return $this->productID( $productID )
                    ->serverIDs($serverIDs)
                    ->new()
                    ->count('game.gameID');
    }
    
    /**
     * initialize fetching game details
     *  
     * @param obj $query
     * @param int $productID
     * @return obj query
     */
    public function scopeGame_info_fields( $query, $is_order = true ) 
    {
        $return = $query->addSelect(
                    'isTestModeEnabled',
                    'gameID', 
                    'gameName', 
                    'type', 
                    'isMobileReady as mobile', 
                    'description', 
                    'imagePath', 
                    'videoReference', 
                    'isNew'
                );

        return ($is_order) ? $return->add_order() : $return;
    }
    
    /**
     * get by product ID
     * 
     * @param type $query
     * @param type $productID
     * @return type
     */
    public function scopeproductID( $query,  $productID)
    {
        
        return $query->where( 'productID','=', $productID );

    }
    /**
     * Filter serverID 
     * @param  object $query     
     * @param  array $serverIDs 
     * @return object
     */
    public function scopeserverIDs($query, $serverIDs )
    {

        return $query->whereNotIn('serverID',$serverIDs);
                    
    }
    
    /**
     * Get by GameID
     * 
     * @param type $query
     * @param type $gameID
     * @return type
     */
    public function scopegameID($query, $gameID)
    {
        if (is_array($gameID)) {

            return $query->whereIn('game.gameID', $gameID);

        } else {

            return $query->where('game.gameID','=', $gameID);

        }
    }
    
    /**
     * New games
     * 
     * @param  object $query
     * @return object
     */
    public function scopeNew( $query ) 
    {
        return $query->where('game.isNew', '=', 1);
    }
        
    /**
     * order data by its release date
     * 
     * @param obj $query
     * @return obj
     */
    public function scopeAdd_order( $query )
    {
        return $query->orderBy('isNew', 'desc')->orderBy('releaseDate', 'desc');
    }

    /**
     * This will get gameName
     * @param  int      
     * @return string
     */
    public function get_gameName($gameID)
    {
        return $this->select('gameName', 'productName')
                    ->product_join()
                    ->gameID($gameID)->first();
    }

    /**
     * This will filter all enabled serverID only
     * @param  object $query    
     * @param  string $serverID
     * @return object
     */
    public function scopeEnabled_serverID($query, $serverID)
    {
        return $query->product_join()
            ->where('game.serverID',             '=', $serverID)
            ->where('game.isTestModeEnabled',    '=', 0)
            ->where('product.isTestModeEnabled', '=', 0);
    }

    /**
     * This will count enabled games and product record under serverID
     * @param  string $serverID
     * @return int
     */
    public function count_enabled_serverID($serverID)
    {
        return $this->enabled_serverID($serverID)->count('game.serverID');
    }

    /**
     * get data of games
     * @param  array $gameIDs 
     * @return object          
     */
    public function games_data($gameIDs)
    {
        return $this->select('game.gameID', 'game.serverID', 'game.gameName')
                    ->product_join()
                    ->gameID($gameIDs)
                    ->get();
    }

    /**
     * get data of games
     * @param  array $gameIDs 
     * @return object          
     */
    public function get_unique_serverIDs($gameIDs)
    {
        return $this->select('game.serverID')
                    ->gameID($gameIDs)
                    ->groupBy('game.serverID')
                    ->pluck('game.serverID');
    }

    /**
     * This will get info of tableID for continuing the game
     * @param  int $clientID
     * @param  int $gameID   
     * @param  int $tableID  
     * @return object
     */
    public function continue_game_info($clientID, $gameID, $tableID)
    {
        return $this->select('tableName','tableID')
                    ->Bjoinable_wallet_field()
                    ->join('usedbalance', 'usedbalance.walletID', '=','game.walletID')
                    ->join('client', 'client.clientID', '=','usedbalance.clientID')
                    ->where('client.clientID', '=', $clientID)
                    ->where('usedbalance.tableID', '=', $tableID)
                    ->where('game.gameID', '=', $gameID)
                    ->where('usedbalance.amount', '>', 0)
                    ->first();
    }

    /**
     * Query all gamesIDs that required displayName
     * @return object 
     */
    public function gameIDs_required_displayName()
    {
        return $this->select('game.gameID')
                    ->where('game.isDisplaynameRequired','=' ,1)
                    ->pluck('game.gameID');
    }

    /**
     * This will get all necessary data for game window
     * @param  int    $gameID    
     * @param  int    $currencyID 
     * @return object
     */
    public function game_window_data($gameID, $currencyID)
    {
        return $this->select('gamecurrency.maxpayout')
            ->join('gamecurrency', function($join) {

                $join->on('gamecurrency.gameID','=','game.gameID')
                    ->on('gamecurrency.currencyID','=',DB::raw('?'));

            })
            ->addBinding($currencyID, 'join')
            ->game_data($gameID);
    }

    /** 
     * This will get serverID of specific gameID
     * @param  int $gameID 
     * @return string
     */
    public function get_serverID($gameID)
    {
        return $this->select('serverID')->gameID($gameID)->value('serverID');
    }
}
