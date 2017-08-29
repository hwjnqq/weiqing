<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/28
 * Time: 14:18
 */

namespace We7\Core;

/**
 * @property array $remote
 * Class We7Setting
 * @package We7\Core
 */
class We7Setting implements \ArrayAccess {

	private static $instance;
	private $loaded = false;
	private $settingdata = array();
	private function __construct() {
//		setting_load();
	}
	public static function getInstance() {
		if(is_null(self::$instance)) {
			self::$instance = new We7Setting();
		}
		return self::$instance;
	}

	/**
	 *  是否上传到云
	 */
	public function isUploadCloud() {
		return isset($this->remote['type']);
	}

	/**
	 *  上传的url
	 * @return mixed
	 */
	public function getUploadUrl() {
		$remotetype = '';
		switch ($this->remote['type']) {
			case ATTACH_FTP : $remotetype = 'ftp'; break;
			case ATTACH_OSS : $remotetype = 'alioss'; break;
			case ATTACH_QINIU : $remotetype = 'qiniu'; break;
			case ATTACH_COS : $remotetype = 'cos'; break;
		}
		$remoteurl  = $this->remote[$remotetype]['url'];
		return $remoteurl;
	}


	public function offsetExists($offset) {
		
	}


	public function offsetGet($offset) {

	}


	public function offsetSet($offset, $value) {

	}


	public function offsetUnset($offset) {

	}
}