<?php

namespace Backend\models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\UserProvider;

use DB;

/**
 * This is a special modal dedicated for laravel Auth ;[]
 */
class Muserprovider extends Mclient implements UserProvider, AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword;

    private $deffered_tables = array(
                                'clientbalance' => array(
                                                    'playableBalance',
                                                    'availableBalance',
                                                    'cashBalance',
                                                    'totalBalance',
                                                    'maxCredit',
                                                    'creditLimit',
                                                    'pokerLimit',
                                                    'pokerAvailableLimit',
                                                    'availableCredit'
                                                ),

                                'clientproduct' => array(
                                                    'productID'
                                                ),

                                'avatar'        => array(
                                                    'filename' => 'avatar_filename'
                                                ),

                                'parent'        => array(
                                                    'username' => 'parent_username',
                                                ),

                                'currency'      => array(
                                                    'code'        => 'currency_code',
                                                    'description' => 'currency_description'
                                                )
                            );

    /**
     * This is a part of UserProvider interface for Auth::user()
     * 
     * @param  int    $clientID
     * @return object
     */
    public function retrieveById($clientID) 
    {
        // get client and clientsession at first
        return $this->select('client.*','clientsession.*')
                    ->account_statuses_field()
                    ->join('clientsession','clientsession.clientID','=','client.clientID')
                    ->where('client.clientID','=',$clientID)
                    ->first();
    }

    /**
     * This is a part of UserProvider interface for Auth::user()
     * Get and return a user by their unique identifier and "remember me" token
     * 
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token) {}

    /**
     * This is a part of UserProvider interface for Auth::user()
     * Save the given "remember me" token for the given user
     * 
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(AuthenticatableContract $user, $token) {}

    /**
     * This is a part of UserProvider interface for Auth::user()
     * Get and return a user by looking up the given credentials
     * 
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials) {}

    /**
     * This will try to find avalue for given field name
     * @param  string $field_name 
     * @return mixed
     */
    public function find_value($field_name)
    {
        $table = array_where_first($this->deffered_tables, function($value, $key) use($field_name) {

                    return in_array($field_name, $value);

                });

        // get data from tables
        if ($table['value']) {

            $fields = array_parameterize($table['value']);

            switch ($table['key']) {

                case 'avatar':

                    $values = DB::table($table['key'])
                                ->select($fields)
                                ->where($table['key'].'.clientID', '=', $this->clientID)
                                ->where($table['key'].'.isActive', '=', 1)
                                ->first();

                    break;

                case 'parent':
                    
                    $values = DB::table('client')
                                ->select($fields)
                                ->where('client.clientID', '=', $this->parentID)
                                ->first();       

                    break;

                case 'currency':
                    
                    $values = DB::table($table['key'])
                                ->select($fields)
                                ->where($table['key'].'.currencyID', '=', $this->currencyID)
                                ->first();         

                    break;

                default:
                    
                    $values = DB::table($table['key'])
                                ->select($fields)
                                ->where($table['key'].'.clientID', '=', $this->clientID)
                                ->first();       

            }

            // merge from exsting Auth::user() data
            if (isset($values) && $values) {

                foreach ($values as $key => &$value) {

                    $value = custom_json_decode($value);
                    $this->setAttribute($key, $value);

                }

                return $values->$field_name;

            }

        }
    }

    /**
     * Validate a user against the given credentials.
     * Check that given credentials belong to the given user
     * 
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials( AuthenticatableContract $user, array $credentials ) {}

    /**
     * We will override the Parent Model __get majic 
     * add our own majic, if the user field wasn't found check from deffered tables
     * @param  string $field_name 
     * @return mixed
     */
    public function __get($field_name)
    {
        $value = Parent::__get($field_name);

        if (!isset($value)) {

            $find_value = $this->find_value($field_name);

            if (isset($find_value)) {
                
                $value = $find_value;

            }

        }

        return $value;
    }
}
