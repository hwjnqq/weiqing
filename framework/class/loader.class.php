<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/class/loader.class.php : v 5a1adce731a4 : 2015/02/05 07:16:41 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

/**
 * @return Loader
 */
function load() {
	static $loader;
	if(empty($loader)) {
		$loader = new Loader();
	}
	return $loader;
}

/**
 * php文件加载器
 */
class Loader {
	
	private $cache = array();
	private $singletonObject = array();
	private $library_map = array(
		'agent' => 'agent/agent.class',
		'captcha' => 'captcha/captcha.class',
		'pdo' => 'pdo/PDO.class',
		'qrcode' => 'qrcode/phpqrcode',
		'ftp' => 'ftp/ftp',
		'pinyin' => 'pinyin/pinyin',
		'pkcs7' => 'pkcs7/pkcs7Encoder',
		'json' => 'json/JSON',
		'phpmailer' => 'PHPMailerAutoload',
	);
	
	function func($name) {
		global $_W;
		if (isset($this->cache['func'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/function/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['func'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Helper Function /framework/function/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function model($name) {
		global $_W;
		if (isset($this->cache['model'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/model/' . $name . '.mod.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['model'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Model /framework/model/' . $name . '.mod.php', E_USER_ERROR);
			return false;
		}
	}
	
	function classs($name) {
		global $_W;
		if (isset($this->cache['class'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/class/' . $name . '.class.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['class'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Class /framework/class/' . $name . '.class.php', E_USER_ERROR);
			return false;
		}
	}
	
	function web($name) {
		global $_W;
		if (isset($this->cache['web'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/web/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['web'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Web Helper /web/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function app($name) {
		global $_W;
		if (isset($this->cache['app'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/app/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['app'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid App Function /app/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	/**
	 * 载入一下库文件
	 * @param string $name path + name
	 */
	function library($name) {
		global $_W;
		if (in_array($name, array_values($this->library_map))) {
			$name = array_search($name, $this->library_map);
		}
		if (isset($this->cache['library'][$name])) {
			return true;
		}
		if (!empty($this->library_map[$name])) {
			$library_name = $this->library_map[$name];
		}
		$file = IA_ROOT . '/framework/library/' . $library_name . '.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['library'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Library /framework/library/' . $name . '.php', E_USER_ERROR);
			return false;
		}
	}
	
	function module($module, $file) {
		if (isset($this->cache['encrypte'][$name])) {
			return true;
		}
		if (strexists(file_get_contents($name), '<?php')) {
			$this->cache['encrypte'][$name] = true;
			require $name;
		} else {
			$key = cache_load('module:cloud:key:1');
			$vars = cache_load('module:cloud:vars:1');
			if (empty($vars)) {
				trigger_error('Module is missing critical files , please reinstall');
			}
			echo <<<EOF
\$_ENV = unserialize(base64_decode('$vars'));
EOF;
			
			
			exit;
		}
	}
	
	/**
	 * 获取一个模型对象或是工具类单例对象
	 * @param unknown $name
	 */
	function singleton($name) {
		if (isset($this->singletonObject[$name])) {
			return $this->singletonObject[$name];
		}
		$this->singletonObject[$name] = $this->object($name);
		return $this->singletonObject[$name];
	}
	
	/**
	 * 获取一个类对象
	 * @param unknown $name
	 */
	function object($name) {
		$this->classs(strtolower($name));
		if (class_exists($name)) {
			return new $name();
		} else {
			return false;
		}
	}
}
