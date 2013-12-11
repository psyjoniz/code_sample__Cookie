<?php

/**
 * 2013.12.11 - Jesse L Quattlebaum (psyjoniz@gmail.com) (https://github.com/psyjoniz/code_sample__Cookie)
 * A class for handling Cookies with complex types.
 *
 * defaults to namespacing
 * only allows one level deep cookie creation though technically you can go deeper; for the purposes
 * of this script and given the nature of cookies we will only go one level deep ($_COOKIE[namespace][item])
 */

class Cookie {

	private $bNamespace        = true;
	private $sDefaultNamespace = 'psyjoniz';
	private $sNamespace;

	/**
	 * constructor with option to override namespace use
	 *
	 * @param boolean|string $mNamespace drives use of namespacing
	 *
	 * @return void
	 */
	public function __construct($mNamespace = true) {
		$this->setNamespace($mNamespace);
	}

	/**
	 * setup the namespace to use when interacting with cookies
	 *
	 * @param boolean|string $mNamespace drives use of namespacing
	 *
	 * @return void
	 */
	private function setNamespace($mNamespace = true) {
		if(false === $mNamespace || null === $mNamespace || trim($mNamespace) == '') {
			$this->unsetNamespace();
		} elseif(true !== $mNamespace) {
			if(true !== $mMessage = $this->validateName($mNamespace)) {
				throw new Exception($mMessage);
			}
			$this->bNamespace = true;
			$this->sNamespace = $mNamespace;
		} else {
			$this->bNamespace = true;
			$this->sNamespace = $this->sDefaultNamespace;
		}
	}

	/**
	 * get the current namespace
	 *
	 * @return string
	 */
	public function getNamespace() {
		return $this->sNamespace;
	}

	/**
	 * turn namespacing off
	 *
	 * @return void
	 */
	private function unsetNamespace() {
		$this->bNamespace = false;
		$this->sNamespace = '';
	}

	/**
	 * validate a cookie name
	 *
	 * @param string $sName name to validate
	 *
	 * @return boolean
	 */
	private function validateName($sName) {
		if(false === $this->bNamespace) {
			$sNormalizedName = preg_replace("/[^a-zA-Z0-9_]/", '', $sName);
			$sNormalizedName = str_replace(' ', '', $sNormalizedName);
			if(null === $sName || !isset($sName) || trim($sName) == '' || $sName != $sNormalizedName) {
				return 'Invalid Cookie name supplied (' . $sName . ').';
			}
		}
		return true;
	}

	/**
	 * get the host for (un)setting a cookie
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
	 * determine what name setCookie() will use
	 *
	 * @param string $sName name to construct actual name from
	 *
	 * @return string
	 */
	private function getNameToSet($sName) {
		if(false === $this->bNamespace) {
			$sNameToSet = $sName;
		} else {
			$sNameToSet = $this->sNamespace . '[' . $sName . ']';
		}
		return $sNameToSet;
	}

	/**
	 * set a cookie
	 *
	 * @param string  $sName    name of cookie
	 * @param mixed   $mValue   value of cookie
	 * @param integer $tsExpire expiration date of cookie
	 *
	 * @return void
	 */
	public function set($sName = null, $mValue = null, $tsExpire = null) {
		if(headers_sent()) {
			throw new Exception('Headers already sent; Cannot set cookie.');
		}
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		$sValueToSet = json_encode(array('content' => serialize($mValue)));
		if(null === $tsExpire) {
			$tsExpire = ( time() + ( 60 * 60 * 24 * 365 ) );
		}
		$sNameToSet = $this->getNameToSet($sName);
		if(false === setCookie($sNameToSet, $sValueToSet, $tsExpire, '/', $this->getHTTPHost(), 0)) {
			throw new Exception('Cookie was not able to be set.');
		}
		//make cookie (un)available right away
		if($tsExpire > time()) {
			if(false !== $this->bNamespace) {
				$_COOKIE[$this->sNamespace][$sName] = $sValueToSet;
			} else {
				$_COOKIE[$sName] = $sValueToSet;
			}
		} else {
			if(false !== $this->bNamespace) {
				unset($_COOKIE[$this->sNamespace][$sName]);
			} else {
				unset($_COOKIE[$sName]);
			}
		}
	}

	/**
	 * get value of a cookie
	 *
	 * @param string $sName name of cookie to get
	 *
	 * @return mixed
	 */
	public function get($sName = null) {
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		if(false === $this->bNamespace) {
			$sValue = $_COOKIE[$sName];
		} else {
			$sValue = $_COOKIE[$this->sNamespace][$sName];
		}
		if(null !== $mValue = json_decode($sValue, true)) {
			if(isset($mValue['content'])) {
				$mValue = unserialize($mValue['content']);
			}
		} else {
			$mValue = $sValue;
		}
		return $mValue;
	}

	/**
	 * remove a single cookie
	 *
	 * @param string $sName name of cookie to remove
	 *
	 * @return void
	 */
	public function remove($sName = null) {
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		$this->set($sName, '', ( time() - ( 60 * 60 * 24 * 365 ) ));
	}

	/**
	 * remove all cookies
	 *
	 * @return void
	 */
	public function removeAll() {
		if(false === $this->bNamespace) {
			$mNode = $_COOKIE;
		} else {
			$mNode = $_COOKIE[$this->sNamespace];
		}
		foreach($mNode as $sKey => $mItem) {
			if(true === $this->bNamespace) {
				$this->remove($sKey);
			} else {
				if(is_array($mItem)) {
					$this->setNamespace($sKey);
					foreach($mItem as $sSubKey => $mSubItem) {
						if(is_array($mSubItem)) {
							error_log('Silently ignoring the removal of a Cookie that is deeper of an array than this code is intended to handle.');
						} else {
							$this->remove($sSubKey);
						}
					}
					$this->unsetNamespace();
				} else {
					$this->remove($sKey);
				}
			}
		}
	}
}
