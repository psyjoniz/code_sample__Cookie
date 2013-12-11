<?php

//defaults to namespacing
//only allows one level deep cookie creation (though technically you can go deeper)

class Cookie {

	private $bNamespace = true;
	private $sDefaultNamespace = 'psy-core';
	private $sNamespace;

	public function __construct($mNamespace = true) {
		$this->setNamespace($mNamespace);
	}

	public function setNamespace($mNamespace = true) {
		if(false === $mNamespace || null === $mNamespace || trim($mNamespace) == '') {
			$this->bNamespace = false;
		} elseif(true !== $mNamespace) {
			if(true !== $mMessage = $this->validateName($mNamespace)) {
				throw new Exception($mMessage);
			}
			$this->sNamespace = $mNamespace;
		} else {
			$this->sNamespace = $this->sDefaultNamespace;
		}
	}

	public function getNamespace() {
		return $this->sNamespace;
	}

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

	private function getHTTPHost() {
		if($_SERVER['HTTP_HOST'] == 'localhost') {
			return false;
		} else {
			return $_SERVER['HTTP_HOST'];
		}
	}

	private function getNameToSet($sName) {
error_log('getNameToSet() : getting name for ' . $sName);
		if(false === $this->bNamespace) {
error_log('getNameToSet() : not using namespace');
			$sNameToSet = $sName;
		} else {
error_log('getNameToSet() : using namespace ' . $this->sNamespace);
			$sNameToSet = $this->sNamespace . '[' . $sName . ']';
		}
error_log('getNameToSet() : returning ' . $sNameToSet);
		return $sNameToSet;
	}

	public function set($sName = null, $mValue = null, $tsExpire = null) {
error_log('set() : setting ' . $sName . ' to ' . $sValue);
		if(headers_sent()) {
			throw new Exception('Headers already sent; Cannot set cookie.');
		}
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		$sValueToSet = json_encode(array('content' => serialize($mValue)));
error_log('set() : json representation : ' . $sValueToSet);
		if(null === $tsExpire) {
			$tsExpire = ( time() + ( 60 * 60 * 24 * 365 ) );
		}
error_log('set() : tsExpire : ' . $tsExpire);
		$sNameToSet = $this->getNameToSet($sName);
error_log('set() : sNameToSet : ' . $sNameToSet);
		if(false === setCookie($sNameToSet, $sValueToSet, $tsExpire, '/', $this->getHTTPHost(), 0)) {
			throw new Exception('Cookie was not able to be set.');
		}
		//make cookie (un)available right away
		if($tsExpire > time()) {
error_log('set() : making available to php');
			if(false !== $this->bNamespace) {
				$_COOKIE[$this->sNamespace][$sName] = $sValueToSet;
			} else {
				$_COOKIE[$sName] = $sValueToSet;
			}
		} else {
error_log('set() : making unavailable to php');
			if(false !== $this->bNamespace) {
				unset($_COOKIE[$this->sNamespace][$sName]);
			} else {
				unset($_COOKIE[$sName]);
			}
		}
	}

	public function get($sName = null) {
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
error_log('get() : getting ' . $sName);
		if(false === $this->bNamespace) {
error_log('get() : no namespace');
			$sValue = $_COOKIE[$sName];
		} else {
error_log('get() : using namespace');
			$sValue = $_COOKIE[$this->sNamespace][$sName];
		}
error_log('get() : sValue : ' . $sValue);
		if(null !== $mValue = json_decode($sValue, true)) {
error_log('get() : mValue : ' . print_r($mValue, true));
			if(isset($mValue['content'])) {
error_log('get() : found \'content\'; unserializing mValue');
				$mValue = unserialize($mValue['content']);
			}
		} else {
error_log('get() : using unencoded value from sValue');
			$mValue = $sValue;
		}
error_log('get() : mValue : ' . print_r($mValue, true));
		return $mValue;
	}

	public function remove($sName = null) {
error_log('remove() : removing ' . $sName);
		if(true !== $sMessage = $this->validateName($sName)) {
			throw new Exception($sMessage);
		}
		$this->set($sName, '', ( time() - ( 60 * 60 * 24 * 365 ) ));
	}

	public function removeAll() {
		if(false === $this->bNamespace) {
error_log('removeAll() : no namespace');
			$mNode = $_COOKIE;
		} else {
error_log('removeAll() : using namespace');
			$mNode = $_COOKIE[$this->sNamespace];
		}
		foreach($mNode as $sKey => $mItem) {
error_log('removeAll() : sKey : ' . $sKey);
error_log('removeAll() : mItem : ' . print_r($mItem, true));
			if(true === $this->bNamespace) {
				$this->remove($sKey);
			} else {
				if(is_array($mItem)) {
					$this->bNamespace = true;
					$this->sNamespace = $sKey;
					foreach($mItem as $sSubKey => $mSubItem) {
						if(is_array($mSubItem)) {
							error_log('Silently ignoring the removal of a Cookie that is deeper of an array than this code is intended to handle.');
						} else {
							$this->remove($sSubKey);
						}
					}
					$this->bNamespace = false;
					$this->sNamespace = '';
				} else {
					$this->remove($sKey);
				}
			}
		}
	}
}
