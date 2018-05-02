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
 * @param string $name 服务名称
 * @return We7Table 表模型
 */
function table($name) {
	load()->classs('table');
	load()->table($name);
	$service = false;

	$class_name = "{$name}Table";
	if (class_exists($class_name)) {
		$service = new $class_name();
	}
	return $service;
}

/**
 * php文件加载器
 *
 * @method boolean func($name)
 * @method boolean model($name)
 * @method boolean classs($name)
 * @method boolean web($name)
 * @method boolean app($name)
 * @method boolean library($name)
 */
class Loader {

	private $cache = array();
	private $singletonObject = array();
	private $libraryMap = array(
		'agent' => 'agent/agent.class',
		'captcha' => 'captcha/captcha.class',
		'pdo' => 'pdo/PDO.class',
		'qrcode' => 'qrcode/phpqrcode',
		'ftp' => 'ftp/ftp',
		'pinyin' => 'pinyin/pinyin',
		'pkcs7' => 'pkcs7/pkcs7Encoder',
		'json' => 'json/JSON',
		'phpmailer' => 'phpmailer/PHPMailerAutoload',
		'oss' => 'alioss/autoload',
		'qiniu' => 'qiniu/autoload',
		'cos' => 'cosv4.2/include',
		'cosv3' => 'cos/include',
		'sentry' => 'sentry/Raven/Autoloader',
	);
	private $loadTypeMap = array(
		'func' => '/framework/function/%s.func.php',
		'model' => '/framework/model/%s.mod.php',
		'classs' => '/framework/class/%s.class.php',
		'library' => '/framework/library/%s.php',
		'table' => '/framework/table/%s.table.php',
		'web' => '/web/common/%s.func.php',
		'app' => '/app/common/%s.func.php',
	);

	public function __call($type, $params) {
		global $_W;
		$name = $cachekey = array_shift($params);
		if (!empty($this->cache[$type]) && isset($this->cache[$type][$cachekey])) {
			return true;
		}
		if (empty($this->loadTypeMap[$type])) {
			return true;
		}
		//第三方库文件因为命名差异，支持定义别名
		if ($type == 'library' && !empty($this->libraryMap[$name])) {
			$name = $this->libraryMap[$name];
		}
		$file = sprintf($this->loadTypeMap[$type], $name);
		if (file_exists(IA_ROOT . $file)) {
			include IA_ROOT . $file;
			$this->cache[$type][$cachekey] = true;
			return true;
		} else {
			trigger_error('Invalid ' . ucfirst($type) . $file, E_USER_WARNING);
			return false;
		}
	}

	/**
	 * 获取一个服务单例，目录是在framework/class目录下
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
	 * 获取一个服务对象，目录是在framework/class目录下
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
