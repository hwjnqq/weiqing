<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/28
 * Time: 9:21
 */
namespace We7\Core;

class We7Request implements \ArrayAccess {

	private $_GPC = array();
	private function __construct() {
		$this->init();
	}

	protected function init() {
		/**
		 * @var $config We7Config
		 */
		$_GPC = array();
		$config = we7app('config');
		if(MAGIC_QUOTES_GPC) {
			$_GET = istripslashes($_GET);
			$_POST = istripslashes($_POST);
			$_COOKIE = istripslashes($_COOKIE);
		}
		$cplen = strlen($config->cookie_pre());
		foreach($_COOKIE as $key => $value) {
			if(substr($key, 0, $cplen) == $config->cookie_pre()) {
				$_GPC[substr($key, $cplen)] = $value;
			}
		}
		$_GPC = array_merge($_GET, $_POST, $_GPC);
		$_GPC = ihtmlspecialchars($_GPC);
		if(!$this->isAjax()) {
			$input = file_get_contents("php://input");
			if (!empty($input)) {
				$__input = @json_decode($input, true);
				if (!empty($__input)) {
					$_GPC['__input'] = $__input;
					$app = we7app();
					$app['isajax'] = true;
				}
			}
			unset($input, $__input);
		}
		$this->_GPC  = $_GPC;
	}

	public static function createRequest() {
		return new We7Request();
	}

	/**
	 *  获取参数
	 * @param $key
	 * @param null $default
	 * @return mixed|null
	 */
	public function get($key, $default = null) {
		if(isset($this->_GPC[$key])) {
			return $this->_GPC[$key];
		}
		return $default;
	}
	/**
	 *  是否是ajax
	 */
	public function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}


	public function isPost() {
		return $this->method() === 'POST';
	}
	/**
	 *  请求方法
	 * @return mixed
	 */
	public function method() {
		return $_SERVER['REQUEST_METHOD'];
	}


	/**
	 *  scriptname
	 * @return string
	 */
	public function scriptname() {
		return htmlspecialchars(scriptname());
	}



	public function siteroot() {
		$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
		$siteroot = htmlspecialchars(we7app('sitescheme') . $_SERVER['HTTP_HOST'] . $sitepath);

		if(substr($siteroot, -1) != '/') {
			$siteroot .= '/';
		}
		$urls = parse_url($siteroot);
		$urls['path'] = str_replace(array('/web', '/app', '/payment/wechat', '/payment/alipay', '/api'), '', $urls['path']);
		return  $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '').$urls['path'];
//		$_W['siteurl'] = $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '') . $_W['script_name'] . (empty($_SERVER['QUERY_STRING'])?'':'?') . $_SERVER['QUERY_STRING'];

	}

	public function siteurl() {
		$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
		$siteroot = htmlspecialchars(we7app('sitescheme') . $_SERVER['HTTP_HOST'] . $sitepath);

		if(substr($siteroot, -1) != '/') {
			$siteroot .= '/';
		}
		$urls = parse_url($siteroot);
		$urls['path'] = str_replace(array('/web', '/app', '/payment/wechat', '/payment/alipay', '/api'), '', $urls['path']);
		return $urls['scheme'].'://'.$urls['host'].((!empty($urls['port']) && $urls['port']!='80') ? ':'.$urls['port'] : '') . $this->scriptname() . (empty($_SERVER['QUERY_STRING'])?'':'?') . $_SERVER['QUERY_STRING'];

	}


	/**
	 *  是否https
	 */
	public function isHttps() {
		return $_SERVER['SERVER_PORT'] == 443 ||
		(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ||
		strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' ||
		strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https' //阿里云判断方式
			? true : false;
	}


	/**
	 *  获取ip
	 * @return string
	 */
	public function ip() {
		static $ip = '';
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
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
		return isset($this->_GPC[$offset]);
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
		if($this->offsetExists($offset)) {
			return $this->_GPC[$offset];
		}
		return null;
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
		if(is_null($offset)) {
			 $this->_GPC[] = $value;
		}else {
			 $this->_GPC[$offset] = $value;
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
		unset($this->_GPC[$offset]);
	}
}