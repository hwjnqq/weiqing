<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/28
 * Time: 9:24
 */
namespace We7\Core;

class We7Config implements \ArrayAccess {

	private static $instance;

	public $isloaded = false;
	private $configfile = '';
	private $config;
	private function __construct() {

		$this->configfile = IA_ROOT . "/data/config.php";
		$this->isloaded = require $this->configfile;
		if ($this->isloaded) {
			$this->config = $config;
			$this->init();
		}

	}

	/**
	 *  配置文件是否存在
	 * @return bool|mixed
	 */
	public function isloaded() {
		return $this->isloaded;
	}

	public static function instance() {
		if(!self::$instance) {
			self::$instance  = new We7Config();
		}
		return self::$instance;
	}

	private function init() {
		$this->config['db']['tablepre'] = !empty($this->config['db']['master']['tablepre']) ? $this->config['db']['master']['tablepre'] : $this->config['db']['tablepre'];
		// 初始化缓存设置
		if(!in_array($this->config['setting']['cache'], array('mysql', 'memcache', 'redis'))) {
			$this->config['setting']['cache'] = 'mysql';
		}
		if(function_exists('date_default_timezone_set')) {
			date_default_timezone_set($this->timezone());
		}

		if(!empty($this->config['setting']['memory_limit']) && function_exists('ini_get') && function_exists('ini_set')) {
			if(@ini_get('memory_limit') != $this->memory_limit()) {
				@ini_set('memory_limit', $this->memory_limit());
			}
		}
	}

	/**
	 *  是否有https 设置
	 * @return bool
	 */
	public function isSetHttps() {
		return isset($this->config['setting']['https']);
	}

	/**
	 *  是否是https
	 */
	public function isHttps() {
		if($this->isSetHttps()) {
			return $this->config['setting']['https'] === 1;
		}
		return false;
	}

	/**
	 *  获取字符集
	 */
	public function charset() {
		return $this->config['setting']['charset'];
	}

	/**
	 *  是否开发者模式
	 */
	public function isDev() {
		return $this->config['setting']['development'] === 1;
	}

	/**
	 *  时区
	 */
	public function timezone() {
		return $this->config['setting']['timezone'];
	}

	/**
	 * 内存大小
	 */
	public function memory_limit() {
		return $this->config['setting']['memory_limit'];
	}

	/**
	 * 获取Cookie 前缀
	 */
	public function cookie_pre() {
		return $this->config['cookie']['pre'];
	}
	/**
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet($offset) {
		return $this->offsetExists($offset) ? $this->config[$offset] : null;
	}

	/**
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->config[] = $value;
		}else {
			$this->config[$offset] = $value;
		}

	}

	/**
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}
}