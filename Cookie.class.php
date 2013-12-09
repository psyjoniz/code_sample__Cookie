<?php

/**
 * 2013.11.30 - Jesse L Quattlebaum (psyjoniz@gmail.com) (https://github.com/psyjoniz/code_sample__Cookie)
 * A class handling Cookies with complex types.
 */

class Cookie {

	private $sNamespace = 'psy-core';

	/**
	 * constructor allowing optional namespace
	 *
	 * @param string $sNamespace name of namespace to be used for data storage within global $_COOKIE
	 * @return void
	 */
	function __construct($sNamespace = null)
	{
		if(null !== $sNamespace) {
			$this->sNamespace = $sNamespace;
		}
	}

	/**
	 * validate the name of a cookie
	 *
	 * @param string $sName name of cookie to validate
	 * @return boolean|string
	 */
	private function validateName($sName)
	{
		$sNormalizedName = preg_replace("/[^a-z0-9]+i/", '', $sName);
		$sNormalizedName = str_replace(' ', '', $sNormalizedName);
		if(null === $sName || !isset($sName) || trim($sName) == '' || $sName != $sNormalizedName) {
			return 'Invalid Cookie name supplied.';
		}
		return true;
	}

	/**
	 * get the host to be used when setting a cookie
	 *
	 * @return boolean|string
	 */
	private function getHTTPHost() {
		if($_SERVER['HTTP_HOST'] == 'localhost') {
			return false;
		} else {
			return $_SERVER['HTTP_HOST'];
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
		if(headers_sent()) {
			throw new Exception('Headers already sent; Cannot set cookie.');
		}
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		if(null === $mValue) {
			throw new Exception('Cookie value not supplied.');
		}
		$sValueToSet = json_encode(array('content' => serialize($mValue)));
		if(null === $tsExpire) {
			$tsExpire = ( time() + ( 60 * 60 * 24 * 365 ) );
		}
		if(false === setCookie($this->sNamespace . "[" . $sName . "]", $sValueToSet, $tsExpire, '/', $this->getHTTPHost(), 0)) {
			throw new Exception('Cookie was not able to be set.');
		}
		$_COOKIE[$this->sNamespace][$sName] = $sValueToSet; // makes cookie available right away to php
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
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		if(!isset($_COOKIE[$this->sNamespace][$sName])) {
			throw new Exception('Cookie \'' . $sName . '\' does not exist.');
		}
		$sValue = $_COOKIE[$this->sNamespace][$sName];
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
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		$this->set($sName, '', ( time() - ( 60 * 60 * 24 * 365 ) ));
		unset($_COOKIE[$this->sNamespace][$sName]); // makes cookie unavailable right away
	}

	/**
	 * remove all cookies
	 *
	 * @return boolean
	 */
	public function removeAll()
	{
		foreach($_COOKIE[$this->sNamespace] as $sName => $mValue) {
			if(false === $this->remove($sName))
			{
				throw new Exception('Remove all failed.');
			}
		}
		$tsExpire = ( time() + ( 60 * 60 * 24 * 365 ) );
		setCookie($this->sNamespace, '', $tsExpire, '/', $this->getHTTPHost(), 0);
		unset($_COOKIE[$this->sNamespace]);
		return true;
	}
}
