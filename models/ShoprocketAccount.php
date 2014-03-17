<?php
class ShoprocketAccount {

	protected static $accountId;
	

	public function login($username, $password){

               return true;
	}

	public function load(){

		return self::$accountId;
	}

}