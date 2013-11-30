<?php

/**
 * 2013.11.30 - Jesse L Quattlebaum (psyjoniz@gmail.com)
 * A quick class for handling cookies.  Also handles complex types.
 *
 * To note: trying to `$this->aStorage &= $_COOKIE[$this->sStorage]` and then
 * `return $this->aStorage[$sName]` did not work for some reason which is why
 * `$_COOKIE[$this->sStorage]` is being used.
 */

class Cookie {

	private $sStorage = 'storage_namespace';

	/**
	 * constructor allowing optional namespace
	 *
	 * @param string $sStorage name of namespace to be used for data storage within global $_COOKIE
	 * @return void
	 */
	function __construct($sStorage = null)
	{
		if(null !== $sStorage) {
			$this->sStorage = $sStorage;
		}
		if(!isset($_COOKIE[$this->sStorage]) || !is_array($_COOKIE[$this->sStorage])) {
			$_COOKIE[$this->sStorage] = array();
		}
	}

	/**
	 * set a cookie
	 *
	 * @param string $sName name of cookie
	 * @param mixed $mValue value of cookie may be pretty much anything (though some complex objects may not store properly)
	 * @param interger $tsExpire expiration timestamp
	 * @return boolean
	 */
	public function set($sName = null, $mValue = null, $tsExpire = null)
	{
		if(null === $sName || !is_string($sName)) {
			throw new Exception('Invalid Cookie name supplied.');
			return false;
		}
		if(null === $mValue) {
			throw new Exception('Cookie value not supplied.');
			return false;
		}
		$sValueToSet = json_encode(array('content' => serialize($mValue)));
		if(null === $tsExpire) {
			$tsExpire = ( time() + ( 60 * 60 * 24 * 365 ) );
		}
		setCookie($this->sStorage . "[" . $sName . "]", $sValueToSet, $tsExpire, '/', $_SERVER['HTTP_HOST'], 0);
		$_COOKIE[$this->sStorage][$sName] = $sValueToSet; // makes cookie available right away to php
		return true;
	}

	/**
	 * get a cookie
	 *
	 * @param string $sName name of cookie
	 * @return boolean|mixed
	 */
	public function get($sName = null)
	{
		if(null === $sName || !is_string($sName)) {
			throw new Exception('Invalid Cookie name supplied.');
			return false;
		}
		if(!isset($_COOKIE[$this->sStorage][$sName])) {
			return false;
		}
		$sValue = $_COOKIE[$this->sStorage][$sName];
		$aValue = json_decode($sValue, true);
		$mReturn = unserialize($aValue['content']);
		return $mReturn;
	}

	/**
	 * remove a cookie
	 *
	 * @param string $sName name of cookie to remove
	 * @return boolean|void
	 */
	public function remove($sName = null)
	{
		if(null === $sName || !is_string($sName)) {
			throw new Exception('Invalid Cookie name supplied.');
			return false;
		}
		$this->set($sName, '', ( time() - ( 60 * 60 * 24 * 365 ) ));
		unset($_COOKIE[$this->sStorage][$sName]); // makes cookie unavailable right away
	}

	/**
	 * remove all cookies
	 *
	 * @return boolean
	 */
	public function removeAll()
	{
		foreach($_COOKIE[$this->sStorage] as $sName => $mValue) {
			if(false === $this->remove($sName))
			{
				throw new Exception('Remove all failed.');
				return false;
			}
		}
		return true;
	}
}
