<?php

namespace Backend\services;

use App;
use Crypt;
use Hashids\Hashids;
use Config;
/**
* Facade for encrytion of Aes and laravel crypt
*/
class Scrypt extends Baseservice {
	
	/**
	 * encryption using laravel crypt
	 * @param  mix $to_encrypt 
	 * @return string             
	 */
	public function crypt_encrypt($to_encrypt)
	{
		
		return Crypt::encrypt($to_encrypt);

	}

	/**
	 * decrypt string using laravel crypt
	 * @param  string $to_decrypt 
	 * @return mix             
	 */
	public function crypt_decrypt($to_decrypt)
	{
		
		return Crypt::decrypt($to_decrypt);

	}

	/**
	 * encryption using AES
	 * @param  string  $toEncrypt 	Data to be encrypted
	 * @param  boolean $no_expire 
	 * @return string
	 */
	public function aes_encrypt($toEncrypt, $no_expire = false)
	{

		return $this->library('Aes')->encrypt($toEncrypt, $no_expire);

	}

	/**
	 * decypt using AES
	 * @param  string  $toDecrypt   Encrypted data
	 * @param  integer $count     
	 * @param  boolean $no_expire 
	 * @return string/array
	 */
	public function aes_decrypt($toDecrypt = '', $count = 1, $no_expire = false)
	{
		$decrypted_data = $this->library('Aes')->decrypt($toDecrypt, $count, $no_expire);

		if ($count > 0) {
			parse_str($decrypted_data,$parsed_data);
			return $parsed_data;
		} else {
			return $decrypted_data;
		}

	}

	/**
	 * This will encode value using hashing
	 * @param  mix  $to_encode  
	 * @param  int $identifier this will use identify if player =1 or guest =2 
	 * @return string              
	 */
	public function hashids_encode($to_encode, $identifier = null)
	{	
		$hashids_config = Config::get('settings.hashids');
        $hashids        = new Hashids($hashids_config['salt'], $hashids_config['padding']);

        if (!is_null($identifier)) {
	        return $hashids->encode(array(
	        		$to_encode, 
	        		$identifier));

        }

        return $hashids->encode($to_encode);

	}

	/**
	 * This will decode value using hashing
	 * @param  string $to_decode 
	 * @return mix     
	 */
	public function hashids_decode($to_decode)
	{	
		$hashids_config = Config::get('settings.hashids');
        $hashids        = new Hashids($hashids_config['salt'], $hashids_config['padding']);

        return $hashids->decode($to_decode);

	}
}