<?php

namespace Backend\libraries;



// The library requires same timezone setting
// for encrypting and decrypting payloads.
date_default_timezone_set('Asia/Manila');

/**
 * @version  1.1
 */
class Aes {
	
	private $encryptionKey;
	
	private $padding 				= 0;

	private $no_expire_padding 		= 'AAAAAAAAAAAAAAAAAAAA';
	
	private $block					= 16;
	
	private $cipher					= 'AES-256-CFB';
	
	private $passwordKey 			= 'AAecoKv0stHMs3x*%E*AJ4do32Eo1iJO';

	private $entropy 				= 2016;



	public function __construct()
	{
		
		$time0 = date('mdYHi');
		$time1 = date('mdYHi', time() - 60);
		$time2 = date('mdYHi', time() - 120);


		// Generate key for 3 minutes
		$this->encryptionKey = array();
		$this->encryptionKey[0] = md5("{$time0}|{$this->passwordKey}");
		$this->encryptionKey[1] = md5("{$time1}|{$this->passwordKey}");
		$this->encryptionKey[2] = md5("{$time2}|{$this->passwordKey}");
	}


	/**
	 *
	 * Encrypts data with AES/CFB/ZERO_PADDING using openssl
	 * Since openssl doesn't support ZERO_PADDING,
	 * this class must do the padding manually.
	 * http://en.wikipedia.org/wiki/Padding_(cryptography)
	 *
	 * @param  string 	$toEncrypt 	Data to be encrypted
	 * @param  boolean 	$no_expire 	Set to:
	 *                              true => without expiry (ex. for client_id's)
	 *                              false => encrypted data has a validity of 3 minutes
	 *                                  	 (ex. for payloads)
	 * 
	 * @return string 	$encode
	 */
	public function encrypt($toEncrypt = '', $no_expire = false)
	{

		if ($no_expire == true) {

			$encryptionKey = md5($this->entropy . '|' . $this->passwordKey);

			$mix = $this->padding;
			
		} else {

			$encryptionKey = $this->encryptionKey[0];

			$mix = mt_rand(0, 15);
		}


		// Check if the data has an invalid block size
		$bytes	= strlen($toEncrypt);
		$bitmod	= $bytes % $this->block;
		if ($bitmod > 0) {

			$bitmis	= $this->block - $bitmod;
			$block	= $bytes + $bitmis;
			
			$padding = $block * 2;
			$hexval	 = bin2hex($toEncrypt);
			
			$hexpad	 = str_pad($hexval, $padding, $this->padding);
			$toEncrypt = $this->hex2bin($hexpad);
		}


		$bytes =  array();
		for ($i = 0; $i <= 15; $i++) {
			array_push($bytes, $mix);
		}


		// Initialization Vector (IV)
  		$this->iv = call_user_func_array("pack", array_merge(array("c*"), $bytes));


		// Encryption
		$encrypt = openssl_encrypt($toEncrypt, $this->cipher, $encryptionKey, OPENSSL_NO_PADDING, $this->iv);
		$encode	 = base64_encode($this->iv . $encrypt);
	

		if ($no_expire === true) {
			$encode = str_replace($this->no_expire_padding, '', $encode);
		}

		return $encode;
	}

	
	/**
	 *
	 * Decrypts data with AES/CFB/ZERO_PADDING using openssl
	 * 
	 * @param  string   $toDecrypt   Encrypted data
	 * @param  integer  $count       Count of expected data to be decoded
	 *                               (ex. for query params: url=32&sampl=3 has a count value of 2)
	 *                               (ex. for normal strings: 20001 has a count value of 0)
	 * @param  boolean 	$no_expire 	 Set to:
	 *                               true => without expiry (ex. for client_id's)
	 *                               false => encrypted data has a validity of 3 minutes
	 *                                  	  (ex. for payloads)
	 * 	 
	 * @return string 	$decode
	 */
	public function decrypt($toDecrypt = '', $count = 1, $no_expire = false)
	{
		$k = 0;
		$data = 0;
		
		if ($no_expire === true) {
			$toDecrypt = $this->no_expire_padding . $toDecrypt;
		}
		
		$decode = base64_decode($toDecrypt);


		// Get the first 16 bytes as Initialization Vector (IV),
		// while the other left will be treated as the encrypted data.
		$decode_iv = substr($decode, 0 ,16);
		$decode_en = str_replace($decode_iv, '', $decode);

		if (strlen($decode_en) > 15) {

			$decode = null;
			
			if ($no_expire === true) {

				$encryptionKey = md5("{$this->entropy}|{$this->passwordKey}");

				$decode = openssl_decrypt($decode_en, $this->cipher, $encryptionKey, OPENSSL_NO_PADDING, $decode_iv);

			} else {
		
				while ((count($data) != $count) && ($k < 3)) {

					$encryptionKey = $this->encryptionKey[$k];	
	
					$decode = openssl_decrypt($decode_en, $this->cipher, $encryptionKey, OPENSSL_NO_PADDING, $decode_iv);
					$data = explode('&', $decode);
					$k++;
				}
			}
			

			return trim($decode);

		} else {
			
			trigger_error("Cannot decrypt value '{$toDecrypt}'");
		}
	}

	private function hex2bin($string)
	{

        $bin = "";
        $len = strlen($string);
        
        for ($i = 0; $i < $len; $i += 2) {
            $bin .= pack("H*", substr($string, $i, 2));
        }

        return $bin;
    }
}
